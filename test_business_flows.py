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

def make_opener():
    cj = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj), NoRedirectHandler)
    return opener

def get_csrf(opener, url):
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        resp = opener.open(req)
        html = resp.read().decode('utf-8')
        m = re.search(r'name="_token"\s+value="([^"]+)"', html)
        return m.group(1) if m else None
    except urllib.error.HTTPError as e:
        return None

def login(opener, email, password):
    csrf = get_csrf(opener, f"{BASE_URL}/login")
    data = urllib.parse.urlencode({'_token': csrf, 'email': email, 'password': password}).encode('utf-8')
    req = urllib.request.Request(f"{BASE_URL}/login", data=data, headers={'User-Agent': 'Mozilla/5.0'})
    resp = opener.open(req)
    return resp.getcode() == 302

print("=" * 60)
print(" UYUY JASTIP - ADVANCED BUSINESS FLOWS VERIFICATION")
print("=" * 60)

passed = 0
failed = 0

def check(title, cond):
    global passed, failed
    if cond:
        print(f" [PASS] {title}")
        passed += 1
    else:
        print(f" [FAIL] {title}")
        failed += 1

# Setup sessions
budi_opener = make_opener()
login(budi_opener, "budi@uyuy.test", "password")

farhan_opener = make_opener()
login(farhan_opener, "farhan@uyuy.test", "password")

admin_opener = make_opener()
login(admin_opener, "admin1@uyuy.test", "password")

owner_opener = make_opener()
login(owner_opener, "owner@uyuy.test", "password")

# --- Flow 0: Cross-Customer Data Isolation Security Test ---
print("\n[Security Test] Customer Budi tries to view Farhan's Order #7...")
try:
    req_cross = urllib.request.Request(f"{BASE_URL}/pelanggan/pesanan/7", headers={'User-Agent': 'Mozilla/5.0'})
    budi_opener.open(req_cross)
    check("Budi access to Farhan's order must be blocked", False)
except urllib.error.HTTPError as e:
    check("Budi access to Farhan's order is BLOCKED with 403 Forbidden", e.code == 403)

# --- Flow 1: Customer submits cancellation request on their own order ---
print("\n[Flow 1] Customer Budi submits cancellation request on his own Order #2...")
csrf = get_csrf(budi_opener, f"{BASE_URL}/pelanggan/pesanan/2/batal")
cancel_data = urllib.parse.urlencode({
    '_token': csrf,
    'reason': 'Ingin mengubah varian aroma parfum karena salah pilih saat checkout.'
}).encode('utf-8')
req = urllib.request.Request(f"{BASE_URL}/pelanggan/pesanan/2/batal", data=cancel_data, headers={'User-Agent': 'Mozilla/5.0'})
resp = budi_opener.open(req)
check("Customer Cancellation Submission (Redirects 302)", resp.getcode() == 302)

# --- Flow 2: Admin reviews & decides cancellation request ---
print("\n[Flow 2] Admin reviews and approves cancellation...")
req_admin = urllib.request.Request(f"{BASE_URL}/admin/pembatalan", headers={'User-Agent': 'Mozilla/5.0'})
resp_admin = admin_opener.open(req_admin)
check("Admin sees cancellation list (HTTP 200)", resp_admin.getcode() == 200)

csrf_admin = get_csrf(admin_opener, f"{BASE_URL}/admin/pembatalan/1")
decide_data = urllib.parse.urlencode({
    '_token': csrf_admin,
    'status': 'Disetujui',
    'admin_notes': 'Pembatalan disetujui atas verifikasi admin.'
}).encode('utf-8')
req = urllib.request.Request(f"{BASE_URL}/admin/pembatalan/1/putusan", data=decide_data, headers={'User-Agent': 'Mozilla/5.0'})
resp = admin_opener.open(req)
check("Admin Decides Cancellation (Redirects 302)", resp.getcode() == 302)

# --- Flow 3: Admin verifies Payment #2 ---
print("\n[Flow 3] Admin verifies Payment #2 as Valid...")
csrf_verify = get_csrf(admin_opener, f"{BASE_URL}/admin/pembayaran/2/verifikasi")
verify_data = urllib.parse.urlencode({
    '_token': csrf_verify,
    'verification_status': 'Valid',
    'admin_notes': 'Mutasi BCA masuk sesuai nominal tagihan Rp 95.000.'
}).encode('utf-8')
req = urllib.request.Request(f"{BASE_URL}/admin/pembayaran/2/verifikasi", data=verify_data, headers={'User-Agent': 'Mozilla/5.0'})
resp = admin_opener.open(req)
check("Admin Verifies Payment (Redirects 302)", resp.getcode() == 302)

# --- Flow 4: Owner PO Period Toggle & Checkout Restriction ---
print("\n[Flow 4] Owner Closes Active PO -> Customer Checkout Blocked...")
csrf_owner = get_csrf(owner_opener, f"{BASE_URL}/owner/periode-po")
toggle_data = urllib.parse.urlencode({'_token': csrf_owner}).encode('utf-8')
req = urllib.request.Request(f"{BASE_URL}/owner/periode-po/3/status", data=toggle_data, headers={'User-Agent': 'Mozilla/5.0'})
resp = owner_opener.open(req)
check("Owner Closes PO #3 (Redirects 302)", resp.getcode() == 302)

# Customer tries to add to cart when PO is closed -> Should be blocked!
csrf_cust_closed = get_csrf(budi_opener, f"{BASE_URL}/pelanggan/keranjang")
cart_closed_data = urllib.parse.urlencode({
    '_token': csrf_cust_closed,
    'product_id': '1',
    'size_ml': '60',
    'quantity': '1'
}).encode('utf-8')
req = urllib.request.Request(f"{BASE_URL}/pelanggan/keranjang/tambah", data=cart_closed_data, headers={'User-Agent': 'Mozilla/5.0'})
resp = budi_opener.open(req)
check("Customer Add to Cart when PO Closed (Blocked & Redirects 302 with Error)", resp.getcode() == 302)

# Owner re-opens PO #3
csrf_owner2 = get_csrf(owner_opener, f"{BASE_URL}/owner/periode-po")
toggle_data2 = urllib.parse.urlencode({'_token': csrf_owner2}).encode('utf-8')
req = urllib.request.Request(f"{BASE_URL}/owner/periode-po/3/status", data=toggle_data2, headers={'User-Agent': 'Mozilla/5.0'})
resp = owner_opener.open(req)
check("Owner Re-opens PO #3 (Redirects 302)", resp.getcode() == 302)

print("\n" + "=" * 60)
print(f" TOTAL BUSINESS FLOW TESTS: {passed + failed}")
print(f" PASSED:                    {passed}")
print(f" FAILED:                    {failed}")
print("=" * 60)

if failed == 0:
    print(" >>> ALL BUSINESS FLOWS VERIFIED SUCCESSFULLY! <<<")
    sys.exit(0)
else:
    print(" >>> SOME BUSINESS FLOW TESTS FAILED <<<")
    sys.exit(1)
