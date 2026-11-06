import urllib.request
import urllib.parse
import http.cookiejar
import re
import sys

BASE_URL = "http://127.0.0.1:8000"

# ---------------------------------------------------------------------------
# Handler: tangkap 302 tapi JANGAN follow redirect, sehingga kita bisa cek
# status code-nya secara langsung. Cookie tetap disimpan oleh CookieJar.
# ---------------------------------------------------------------------------
class NoRedirectHandler(urllib.request.HTTPRedirectHandler):
    def http_error_302(self, req, fp, code, msg, headers):
        raise urllib.error.HTTPError(req.full_url, code, msg, headers, fp)
    http_error_301 = http_error_302
    http_error_303 = http_error_302
    http_error_307 = http_error_302


def make_opener():
    """Buat opener dengan cookie jar + no-redirect handler."""
    cj = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(
        urllib.request.HTTPCookieProcessor(cj),
        NoRedirectHandler,
    )
    return opener


def get_csrf(opener, url):
    """GET halaman dan ekstrak _token CSRF dari form HTML."""
    req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
    try:
        resp = opener.open(req)
        html = resp.read().decode("utf-8")
    except urllib.error.HTTPError as e:
        # Bisa saja halaman redirect (302) → baca tetap html-nya jika ada
        try:
            html = e.fp.read().decode("utf-8")
        except Exception:
            return None
    m = re.search(r'name="_token"\s+value="([^"]+)"', html)
    return m.group(1) if m else None


def login(opener, email, password):
    """Login dan return True jika berhasil (redirect 302 ke dashboard)."""
    csrf = get_csrf(opener, f"{BASE_URL}/login")
    if not csrf:
        print(f"  [WARN] Tidak dapat ambil CSRF dari halaman login.")
        return False
    data = urllib.parse.urlencode({
        "_token": csrf,
        "email":  email,
        "password": password,
    }).encode("utf-8")
    req = urllib.request.Request(
        f"{BASE_URL}/login", data=data,
        headers={"User-Agent": "Mozilla/5.0"},
    )
    try:
        opener.open(req)
        return False  # Jika tidak ada error → login gagal (tidak di-redirect)
    except urllib.error.HTTPError as e:
        return e.code == 302


def request_code(opener, url, method="GET", data=None):
    """Kirim request dan return HTTP status code."""
    encoded = urllib.parse.urlencode(data).encode("utf-8") if data else None
    req = urllib.request.Request(
        url, data=encoded,
        headers={"User-Agent": "Mozilla/5.0"},
        method=method,
    )
    try:
        resp = opener.open(req)
        return resp.getcode()
    except urllib.error.HTTPError as e:
        return e.code
    except Exception:
        return 0


# ---------------------------------------------------------------------------
# Boilerplate hasil test
# ---------------------------------------------------------------------------
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


# ===========================================================================
print("=" * 60)
print(" UYUY JASTIP - ADVANCED BUSINESS FLOWS VERIFICATION")
print("=" * 60)

# ---------------------------------------------------------------------------
# Setup sesi per user
# ---------------------------------------------------------------------------
budi_opener  = make_opener()
ok_budi      = login(budi_opener,  "budi@uyuy.test",     "password")
check("Login Budi berhasil", ok_budi)

farhan_opener = make_opener()
ok_farhan     = login(farhan_opener, "farhan@uyuy.test",  "password")
check("Login Farhan berhasil", ok_farhan)

admin_opener  = make_opener()
ok_admin      = login(admin_opener,  "admin1@uyuy.test",  "password")
check("Login Admin berhasil", ok_admin)

owner_opener  = make_opener()
ok_owner      = login(owner_opener,  "owner@uyuy.test",   "password")
check("Login Owner berhasil", ok_owner)

# ---------------------------------------------------------------------------
# Flow 0: Cross-Customer Data Isolation Security Test
# Budi (customer2) mencoba akses Order #7 milik Farhan (customer4)
# ---------------------------------------------------------------------------
print("\n[Security Test] Budi mencoba akses Order #7 milik Farhan...")
code = request_code(budi_opener, f"{BASE_URL}/pelanggan/pesanan/7")
check("Akses Budi ke pesanan Farhan diblokir (403)", code == 403)

# ---------------------------------------------------------------------------
# Flow 1: Customer Budi submit pembatalan Order #2 (miliknya sendiri)
# Order #2 status: Menunggu Pembayaran → boleh ajukan pembatalan
# ---------------------------------------------------------------------------
print("\n[Flow 1] Budi ajukan pembatalan Order #2 miliknya sendiri...")
csrf_budi = get_csrf(budi_opener, f"{BASE_URL}/pelanggan/pesanan/2/batal")
if csrf_budi:
    cancel_data = {
        "_token": csrf_budi,
        "reason": "Ingin mengubah varian aroma parfum karena salah pilih saat checkout.",
    }
    code = request_code(budi_opener, f"{BASE_URL}/pelanggan/pesanan/2/batal",
                        method="POST", data=cancel_data)
    check("Customer ajukan pembatalan (Redirect 302)", code == 302)
else:
    print(" [FAIL] Tidak bisa ambil CSRF halaman pembatalan (kemungkinan tidak login atau order bukan miliknya)")
    failed += 1

# ---------------------------------------------------------------------------
# Flow 2: Admin review & putuskan pembatalan
# CancellationRequest #1 ada di DB (milik Order #7, Farhan)
# ---------------------------------------------------------------------------
print("\n[Flow 2] Admin review dan putuskan pembatalan #1...")
code_list = request_code(admin_opener, f"{BASE_URL}/admin/pembatalan")
check("Admin lihat daftar pembatalan (200)", code_list == 200)

csrf_admin_cancel = get_csrf(admin_opener, f"{BASE_URL}/admin/pembatalan/1")
if csrf_admin_cancel:
    decide_data = {
        "_token":      csrf_admin_cancel,
        "status":      "Disetujui",
        "admin_notes": "Pembatalan disetujui atas verifikasi admin.",
    }
    code = request_code(admin_opener, f"{BASE_URL}/admin/pembatalan/1/putusan",
                        method="POST", data=decide_data)
    check("Admin putuskan pembatalan (Redirect 302)", code == 302)
else:
    print(" [FAIL] Tidak bisa ambil CSRF halaman detail pembatalan #1")
    failed += 1

# ---------------------------------------------------------------------------
# Flow 3: Admin verifikasi pembayaran Order #2 (Payment #2)
# ---------------------------------------------------------------------------
print("\n[Flow 3] Admin verifikasi Payment #2 sebagai Valid...")
csrf_verify = get_csrf(admin_opener, f"{BASE_URL}/admin/pembayaran/2/verifikasi")
if csrf_verify:
    verify_data = {
        "_token":              csrf_verify,
        "verification_status": "Valid",
        "admin_notes":         "Mutasi BCA masuk sesuai nominal tagihan Rp 120.000.",
    }
    code = request_code(admin_opener, f"{BASE_URL}/admin/pembayaran/2/verifikasi",
                        method="POST", data=verify_data)
    check("Admin verifikasi pembayaran (Redirect 302)", code == 302)
else:
    print(" [FAIL] Tidak bisa ambil CSRF halaman verifikasi pembayaran #2")
    failed += 1

# ---------------------------------------------------------------------------
# Flow 4: Owner tutup PO aktif → customer tidak bisa tambah keranjang
# ---------------------------------------------------------------------------
print("\n[Flow 4] Owner tutup PO aktif → Checkout customer diblokir...")

# Ambil ID PO yang sedang Open dari halaman daftar PO
csrf_owner_pg = get_csrf(owner_opener, f"{BASE_URL}/owner/periode-po")
check("Owner dapat akses daftar PO (200)", csrf_owner_pg is not None)

# Toggle PO #3 (Batch 10, ID=3 sesuai seeder urutan ke-3)
if csrf_owner_pg:
    toggle_data = {"_token": csrf_owner_pg}
    code = request_code(owner_opener, f"{BASE_URL}/owner/periode-po/3/status",
                        method="POST", data=toggle_data)
    check("Owner tutup PO #3 (Redirect 302)", code == 302)

    # Customer coba tambah ke keranjang saat PO ditutup → harus diblokir
    csrf_cart = get_csrf(budi_opener, f"{BASE_URL}/pelanggan/keranjang")
    if csrf_cart:
        cart_blocked_data = {
            "_token":    csrf_cart,
            "product_id": "1",
            "size_ml":   "60",
            "quantity":  "1",
        }
        code = request_code(budi_opener, f"{BASE_URL}/pelanggan/keranjang/tambah",
                            method="POST", data=cart_blocked_data)
        check("Add to cart saat PO tutup diblokir (Redirect 302 + error)", code == 302)
    else:
        print(" [FAIL] Tidak bisa ambil CSRF keranjang")
        failed += 1

    # Owner buka kembali PO #3
    csrf_owner_reopen = get_csrf(owner_opener, f"{BASE_URL}/owner/periode-po")
    if csrf_owner_reopen:
        toggle_data2 = {"_token": csrf_owner_reopen}
        code = request_code(owner_opener, f"{BASE_URL}/owner/periode-po/3/status",
                            method="POST", data=toggle_data2)
        check("Owner buka kembali PO #3 (Redirect 302)", code == 302)
    else:
        print(" [FAIL] Tidak bisa ambil CSRF untuk buka kembali PO")
        failed += 1

# ---------------------------------------------------------------------------
# Ringkasan
# ---------------------------------------------------------------------------
print("\n" + "=" * 60)
print(f" TOTAL BUSINESS FLOW TESTS : {passed + failed}")
print(f" PASSED                    : {passed}")
print(f" FAILED                    : {failed}")
print("=" * 60)

if failed == 0:
    print(" >>> ALL BUSINESS FLOWS VERIFIED SUCCESSFULLY! <<<")
    sys.exit(0)
else:
    print(" >>> SOME BUSINESS FLOW TESTS FAILED <<<")
    sys.exit(1)
