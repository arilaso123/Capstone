import urllib.request
import urllib.parse
import http.cookiejar
import re
import sys

BASE_URL = "http://127.0.0.1:8000"

class NoRedirectHandler(urllib.request.HTTPRedirectHandler):
    def http_error_302(self, req, fp, code, msg, headers):
        return fp
    http_error_301 = http_error_302
    http_error_303 = http_error_302
    http_error_307 = http_error_302

def make_opener(follow_redirects=True):
    cj = http.cookiejar.CookieJar()
    if follow_redirects:
        opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
    else:
        opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj), NoRedirectHandler)
    return opener, cj

passed = 0
failed = 0

def check(title, expected, actual, condition=None):
    global passed, failed
    ok = (expected == actual) if condition is None else condition
    if ok:
        print(f" [PASS] {title} (Status: {actual})")
        passed += 1
    else:
        print(f" [FAIL] {title} (Expected {expected}, Got {actual})")
        failed += 1

def get_csrf(opener, url):
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        resp = opener.open(req)
        html = resp.read().decode('utf-8')
        m = re.search(r'name="_token"\s+value="([^"]+)"', html)
        if m:
            return m.group(1)
        m2 = re.search(r'content="([^"]+)"\s+name="csrf-token"', html)
        if m2:
            return m2.group(1)
        m3 = re.search(r'name="csrf-token"\s+content="([^"]+)"', html)
        if m3:
            return m3.group(1)
    except Exception as e:
        print(f"Error fetching CSRF: {e}")
    return None

def login(opener, email, password):
    csrf = get_csrf(opener, f"{BASE_URL}/login")
    if not csrf:
        return False, 0
    data = urllib.parse.urlencode({
        '_token': csrf,
        'email': email,
        'password': password
    }).encode('utf-8')
    req = urllib.request.Request(f"{BASE_URL}/login", data=data, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        resp = opener.open(req)
        code = resp.getcode()
        return True, code
    except urllib.error.HTTPError as e:
        return True, e.code
    except Exception as e:
        print(f"Login error: {e}")
        return False, 0

def request_code(opener, url, method="GET", data=None):
    encoded_data = urllib.parse.urlencode(data).encode('utf-8') if data else None
    req = urllib.request.Request(url, data=encoded_data, headers={'User-Agent': 'Mozilla/5.0'}, method=method)
    try:
        resp = opener.open(req)
        return resp.getcode()
    except urllib.error.HTTPError as e:
        return e.code
    except Exception as e:
        return 0

print("=" * 60)
print(" UYUY JASTIP - AUTOMATED END-TO-END TEST SUITE")
print("=" * 60)

# 1. Public Routes
print("\n--- 1. Testing Public Routes ---")
guest_opener, _ = make_opener(follow_redirects=False)
for name, path in [
    ("Home Page", "/"),
    ("Catalog Page", "/katalog"),
    ("Product Detail Page (#1)", "/produk/1"),
    ("PO Info Page", "/informasi-po"),
    ("Login Page", "/login"),
    ("Register Page", "/register"),
]:
    code = request_code(guest_opener, f"{BASE_URL}{path}")
    check(name, 200, code)

# 2. Guest Access to Protected Routes
print("\n--- 2. Testing Guest Access to Protected Routes (Must Redirect 302) ---")
for name, path in [
    ("Guest -> Customer Dashboard", "/pelanggan/dashboard"),
    ("Guest -> Admin Dashboard", "/admin/dashboard"),
    ("Guest -> Owner Dashboard", "/owner/dashboard"),
]:
    code = request_code(guest_opener, f"{BASE_URL}{path}")
    check(name, 302, code)

# 3. Customer Role Tests
print("\n--- 3. Testing Customer Authentication & Role Isolation ---")
cust_opener, _ = make_opener(follow_redirects=False)
ok, code = login(cust_opener, "customer@uyuy.test", "password")
check("Customer Login Request", 302, code)

for name, path in [
    ("Customer -> Dashboard", "/pelanggan/dashboard"),
    ("Customer -> Cart", "/pelanggan/keranjang"),
    ("Customer -> Orders Index", "/pelanggan/pesanan"),
    ("Customer -> Order Show #1", "/pelanggan/pesanan/1"),
    ("Customer -> Payment View #1", "/pelanggan/pesanan/1/pembayaran"),
    ("Customer -> Cancellation View #1", "/pelanggan/pesanan/1/batal"),
]:
    code = request_code(cust_opener, f"{BASE_URL}{path}")
    check(name, 200, code)

# Customer unauthorized attempts
for name, path in [
    ("Customer -> Admin Dashboard (Should be 403)", "/admin/dashboard"),
    ("Customer -> Admin Orders (Should be 403)", "/admin/pesanan"),
    ("Customer -> Owner Dashboard (Should be 403)", "/owner/dashboard"),
    ("Customer -> Owner PO Periods (Should be 403)", "/owner/periode-po"),
]:
    code = request_code(cust_opener, f"{BASE_URL}{path}")
    check(name, 403, code)

# 4. Admin Role Tests
print("\n--- 4. Testing Admin Authentication & Role Isolation ---")
admin_opener, _ = make_opener(follow_redirects=False)
ok, code = login(admin_opener, "admin1@uyuy.test", "password")
check("Admin Login Request", 302, code)

for name, path in [
    ("Admin -> Dashboard", "/admin/dashboard"),
    ("Admin -> Orders Index", "/admin/pesanan"),
    ("Admin -> Order Show #1", "/admin/pesanan/1"),
    ("Admin -> Payments Index", "/admin/pembayaran"),
    ("Admin -> Payment Verify #1", "/admin/pembayaran/1/verifikasi"),
    ("Admin -> Cancellations Index", "/admin/pembatalan"),
    ("Admin -> Cancellation Show #1", "/admin/pembatalan/1"),
]:
    code = request_code(admin_opener, f"{BASE_URL}{path}")
    check(name, 200, code)

# Admin unauthorized attempts
for name, path in [
    ("Admin -> Owner Dashboard (Should be 403)", "/owner/dashboard"),
    ("Admin -> Owner PO Periods (Should be 403)", "/owner/periode-po"),
    ("Admin -> Owner Products (Should be 403)", "/owner/produk"),
    ("Admin -> Owner Recap (Should be 403)", "/owner/rekapitulasi"),
    ("Admin -> Owner Customers (Should be 403)", "/owner/pelanggan"),
]:
    code = request_code(admin_opener, f"{BASE_URL}{path}")
    check(name, 403, code)

# 5. Owner Role Tests
print("\n--- 5. Testing Owner Authentication & Role Isolation ---")
owner_opener, _ = make_opener(follow_redirects=False)
ok, code = login(owner_opener, "owner@uyuy.test", "password")
check("Owner Login Request", 302, code)

for name, path in [
    ("Owner -> Dashboard", "/owner/dashboard"),
    ("Owner -> PO Periods Index", "/owner/periode-po"),
    ("Owner -> Create PO Period", "/owner/periode-po/tambah"),
    ("Owner -> Products Index", "/owner/produk"),
    ("Owner -> Create Product", "/owner/produk/tambah"),
    ("Owner -> Edit Product #1", "/owner/produk/1/edit"),
    ("Owner -> Recap Index", "/owner/rekapitulasi"),
    ("Owner -> Customers Index", "/owner/pelanggan"),
]:
    code = request_code(owner_opener, f"{BASE_URL}{path}")
    check(name, 200, code)

# Owner unauthorized attempts (Owner is not admin role)
code = request_code(owner_opener, f"{BASE_URL}/admin/dashboard")
check("Owner -> Admin Dashboard (Should be 403)", 403, code)

# 6. Functional Flow: Cart & Checkout
print("\n--- 6. Functional Flow: Add to Cart & Checkout ---")
csrf_cust = get_csrf(cust_opener, f"{BASE_URL}/pelanggan/keranjang")
cart_data = {
    '_token': csrf_cust,
    'product_id': '2',
    'size_ml': '60',
    'quantity': '2'
}
code = request_code(cust_opener, f"{BASE_URL}/pelanggan/keranjang/tambah", method="POST", data=cart_data)
check("Customer Add to Cart (Redirects 302)", 302, code)

# Checkout
csrf_checkout = get_csrf(cust_opener, f"{BASE_URL}/pelanggan/keranjang")
checkout_data = {
    '_token': csrf_checkout,
    'recipient_name': 'Raka Testing Automasi',
    'recipient_phone': '081234567890',
    'shipping_address': 'Jl. Testing Otomatis No. 99, Jakarta',
    'customer_notes': 'Pesanan hasil pengetesan sistem'
}
code = request_code(cust_opener, f"{BASE_URL}/pelanggan/checkout", method="POST", data=checkout_data)
check("Customer Checkout Post (Redirects 302)", 302, code)

# 7. Functional Flow: Admin Status Transition
print("\n--- 7. Functional Flow: Admin Status Transition ---")
csrf_admin = get_csrf(admin_opener, f"{BASE_URL}/admin/pesanan/1")
status_data = {
    '_token': csrf_admin,
    'new_status': 'Diproses',
    'remarks': 'Barang sedang dibelanjakan ke vendor In Parfume'
}
code = request_code(admin_opener, f"{BASE_URL}/admin/pesanan/1/status", method="POST", data=status_data)
check("Admin Update Status (Redirects 302)", 302, code)

print("\n" + "=" * 60)
print(f" TOTAL TESTS: {passed + failed}")
print(f" PASSED:      {passed}")
print(f" FAILED:      {failed}")
print("=" * 60)

if failed == 0:
    print(" >>> ALL TESTS PASSED WITH 100% SUCCESS RATE! <<<")
    sys.exit(0)
else:
    print(" >>> SOME TESTS FAILED <<<")
    sys.exit(1)

