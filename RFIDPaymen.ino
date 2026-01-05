// --- LIBRARY ---
#include <Wire.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <Keypad.h>
#include <LiquidCrystal_I2C.h>
#include <Adafruit_PN532.h>

// --- NFC & LCD ---
#define SDA_PIN 21
#define SCL_PIN 22
Adafruit_PN532 nfc(SDA_PIN, SCL_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2);

// --- WIFI ---
const char* ssid     = "Nugrah";
const char* password = "123456789";

// --- KEYPAD ---
const byte ROWS = 4, COLS = 4;
char keys[ROWS][COLS] = {
  {'1','2','3','A'},
  {'4','5','6','B'},
  {'7','8','9','C'},
  {'*','0','#','D'}
};
byte rowPins[ROWS] = {13, 12, 14, 27};
byte colPins[COLS] = {26, 25, 33, 32};
Keypad keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);

// --- PROTOTYPE ---
String readInput(bool allowCancel, bool hideInput, bool isEmail = false);
bool cekUser(const String& uid);
bool cekPinExists(const String& uid);
void registerUser(const String& uid);
void registerPin(const String& uid, unsigned long* serverTime = nullptr);
String postForm(const String& url, const String& body, unsigned long* serverTime = nullptr);
void tampilkanMenu(const String& uid);
void cekSaldo(const String& uid, unsigned long waktu_mulai);
void prosesBayar(const String& uid, unsigned long waktu_mulai);
void prosesTopup(const String& uid, unsigned long waktu_mulai);
void otpFlow(const String& uidStr, bool& valid);

// --- SETUP ---
void setup() {
  Serial.begin(115200);
  Wire.begin(SDA_PIN, SCL_PIN);
  lcd.init();
  lcd.backlight();

  lcd.print("Menghubung WiFi");
  WiFi.begin(ssid, password);
  unsigned long t0 = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - t0 < 15000) {
    delay(500);
  }
  lcd.clear();
  lcd.print(WiFi.status() == WL_CONNECTED ? "WiFi Tersambung" : "Gagal Konek");
  delay(1000);
  lcd.clear();

  nfc.begin();
  nfc.SAMConfig();
}

// --- LOOP ---
void loop() {
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("Tempelkan Kartu");

  uint8_t uid[7], uidLen;
  while (!nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, uid, &uidLen)) {
    delay(100);
  }
  String uidStr;
  for (int i = 0; i < uidLen; i++) {
    if (uid[i] < 0x10) uidStr += "0";
    uidStr += String(uid[i], HEX);
  }
  uidStr.toUpperCase();
  Serial.println(">> UID: " + uidStr);

  if (!cekUser(uidStr)) {
    registerUser(uidStr);
    return;
  }

  lcd.clear();
  lcd.print("A:PIN  B:OTP");
  unsigned long tStart = millis();
  char pilih = 0;
  while ((pilih != 'A' && pilih != 'B') && (millis() - tStart < 5000)) {
    char key = keypad.getKey();
    if (key == 'A' || key == 'B') {
      pilih = key;
      break;
    }
    delay(50);
  }

  if (pilih != 'A' && pilih != 'B') {
    lcd.clear();
    lcd.print("Kembali");
    delay(1000);
    return;
  }

  bool valid = false;
  if (pilih == 'A') {
    if (!cekPinExists(uidStr)) {
      lcd.clear(); lcd.print("PIN belum ada");
      lcd.setCursor(0,1); lcd.print("Tekan D utk PIN");
      unsigned long start = millis();
      while (millis() - start < 15000) {
        if (keypad.getKey() == 'D') {
          while (keypad.getKey() != NO_KEY) delay(10);
          unsigned long waktu_server = 0;
          registerPin(uidStr, &waktu_server);
          break;
        }
      }
      return;
    }

    int percobaanSalah = 0;
    while (!valid) {
      lcd.clear(); lcd.print("MASUKKAN PIN");
      lcd.setCursor(0,1);
      String pin = readInput(true, true, false);
      if (pin.length() == 0) return;

      HTTPClient http;
      String url = "http://192.168.137.1/sistem_paymen/api/cek_pin.php?uid=" + uidStr + "&pin=" + pin;
      http.begin(url);
      int code = http.GET();
      String res = http.getString();
      http.end();

      if (res == "VALID") {
        valid = true;
        lcd.clear(); lcd.print("Login Berhasil");
        Serial.println("[LOGIN] PIN valid — Login Berhasil ✅");
        delay(1500);
        break;
      }

      percobaanSalah++;
      lcd.clear(); lcd.print("PIN Salah!");
      lcd.setCursor(0,1); lcd.print("Percobaan: ");
      lcd.print(percobaanSalah);
      Serial.printf("[LOGIN] PIN salah (Percobaan ke-%d)\n", percobaanSalah);
      delay(2000);

      if (percobaanSalah >= 3) {
        lcd.clear(); lcd.print("3x Salah PIN!");
        lcd.setCursor(0,1); lcd.print("Verifikasi OTP");
        delay(2000);
        otpFlow(uidStr, valid);
        if (valid) {
          unsigned long waktu_server = 0;
          registerPin(uidStr, &waktu_server);
          percobaanSalah = 0;
          continue;
        } else {
          lcd.clear(); lcd.print("OTP Gagal!");
          Serial.println("[LOGIN] OTP gagal setelah 3x salah PIN ❌");
          delay(2000);
          return;
        }
      }

      lcd.clear(); lcd.print("C: Reset PIN via OTP");
      unsigned long startReset = millis();
      bool cPressed = false;
      while (millis() - startReset < 3000) {
        char k = keypad.getKey();
        if (k == 'C') {
          cPressed = true;
          break;
        }
        delay(50);
      }

      if (cPressed) {
        lcd.clear(); lcd.print("Kirim OTP...");
        otpFlow(uidStr, valid);
        if (valid) {
          unsigned long waktu_server = 0;
          registerPin(uidStr, &waktu_server);
          percobaanSalah = 0;
          continue;
        }
      }
    }
  } 
  else if (pilih == 'B') {
    otpFlow(uidStr, valid);
    if (!valid) return;
  }

  if (valid) {
    tampilkanMenu(uidStr);
  }
}

// --- DEFINISI FUNGSI ---
String readInput(bool allowCancel, bool hideInput, bool isEmail) {
  String s;
  lcd.noBlink();
  while (true) {
    char k = keypad.getKey();
    if (!k) continue;
    if (k == '#') break;
    if (k == '*' && allowCancel) return "";
    if (!isEmail && k >= '0' && k <= '9') {
      s += k;
      lcd.print(hideInput ? '*' : k);
    }
  }
  return s;
}

// === FUNGSI OTP DENGAN PENGUKURAN WAKTU ===
void otpFlow(const String& uidStr, bool& valid) {
  HTTPClient http;
  http.begin("http://192.168.137.1/sistem_paymen/api/cek_email_exists.php?uid=" + uidStr);
  int code = http.GET();
  String res = http.getString();
  http.end();

  if (res == "NO_EMAIL") {
    lcd.clear(); lcd.print("Email blm ada!");
    lcd.setCursor(0,1); lcd.print("Isi di web admin");
    delay(3500);
    return;
  }

  lcd.clear(); lcd.print("Kirim OTP...");
  unsigned long startSend = millis();
  String otpResp = postForm("http://192.168.137.1/sistem_paymen/api/request_otp.php", "uid=" + uidStr);
  unsigned long endSend = millis();
  unsigned long waktuKirim = endSend - startSend;

  Serial.printf("[WAKTU] Kirim OTP ke email: %lu ms (%.2f detik)\n", waktuKirim, waktuKirim / 1000.0);

  if (otpResp == "OTP_SENT") {
    lcd.clear(); lcd.print("Input OTP:");
    lcd.setCursor(0,1);

    unsigned long startVerify = millis();
    String otp = readInput(false, true, false);
    String verify = postForm("http://192.168.137.1/sistem_paymen/api/verify_otp.php", "uid=" + uidStr + "&otp=" + otp);
    unsigned long endVerify = millis();
    unsigned long waktuVerifikasi = endVerify - startVerify;

    Serial.printf("[WAKTU] Verifikasi OTP: %lu ms (%.2f detik)\n", waktuVerifikasi, waktuVerifikasi / 1000.0);

    if (verify == "OTP_VALID") {
      valid = true;
      lcd.clear(); lcd.print("Login Berhasil");
      Serial.println("[LOGIN] OTP valid — Login Berhasil ✅");
      delay(2000);
    } else {
      lcd.clear(); lcd.print("OTP Salah");
      Serial.println("[LOGIN] OTP salah — Login Gagal ❌");
      delay(2000);
    }
  } else {
    lcd.clear(); lcd.print("OTP Gagal");
    Serial.println("[ERROR] Gagal mengirim OTP ke email ❌");
    delay(2000);
  }
}

bool cekUser(const String& uid) {
  HTTPClient http;
  http.begin("http://192.168.137.1/sistem_paymen/api/cek_user.php?uid=" + uid);
  int code = http.GET();
  String res = http.getString(); http.end();
  return res == "ADA";
}

bool cekPinExists(const String& uid) {
  HTTPClient http;
  http.begin("http://192.168.137.1/sistem_paymen/api/cek_pin_exists.php?uid=" + uid);
  int code = http.GET();
  String res = http.getString(); http.end();
  return res == "ADA_PIN";
}

String postForm(const String& url, const String& body, unsigned long* serverTime) {
  HTTPClient http;
  http.begin(url);
  http.addHeader("Content-Type","application/x-www-form-urlencoded");
  unsigned long waktu_mulai = millis();
  int code = http.POST(body);
  String res = http.getString();
  unsigned long waktu_selesai = millis();
  if (serverTime != nullptr) {
    *serverTime = waktu_selesai - waktu_mulai;
  }
  http.end();
  return res;
}

// === REGISTER USER & PIN ===
void registerUser(const String& uid) {
  lcd.clear(); lcd.print("UID tdk dikenal");
  lcd.setCursor(0,1); lcd.print("Tekan D daftar");
  unsigned long start = millis();
  bool daftar = false;
  while (millis() - start < 15000) {
    if (keypad.getKey() == 'D') {
      while (keypad.getKey() != NO_KEY) delay(10);
      unsigned long waktu_server = 0;
      String resp = postForm("http://192.168.137.1/sistem_paymen/api/daftar_user.php", "uid=" + uid, &waktu_server);
      lcd.clear(); lcd.print(resp == "BERHASIL" ? "User OK" : "Gagal");
      delay(2000);
      daftar = true;
      break;
    }
  }
  if (!daftar) {
    Serial.println("[INFO] Daftar User Dibatalkan/User tidak menekan D.");
  }
}

void registerPin(const String& uid, unsigned long* serverTime) {
  String pin;
  do {
    lcd.clear(); lcd.print("Input PIN 4-6");
    lcd.setCursor(0,1);
    pin = readInput(false, true, false);
  } while (pin.length() < 4 || pin.length() > 6);

  // --- LOG TAMBAHAN ---
  Serial.println("[PIN BARU] User memasukkan PIN baru: " + pin);

  String resp = postForm(
    "http://192.168.137.1/sistem_paymen/api/daftar_pin.php",
    "uid=" + uid + "&pin=" + pin,
    serverTime
  );

  // --- LOG TAMBAHAN ---
  if (resp == "BERHASIL") {
    Serial.println("[PIN BARU] PIN baru berhasil disimpan di server untuk UID: " + uid);
  }

  lcd.clear();
  lcd.print(resp == "BERHASIL" ? "PIN OK" : "Gagal");
  delay(2000);
}

// === MENU UTAMA ===
void tampilkanMenu(const String& uid) {
  bool keluar = false;
  while (!keluar) {
    lcd.clear(); lcd.print("A:Saldo B:Bayar");
    lcd.setCursor(0,1); lcd.print("C:Topup *:Back");
    unsigned long lastKeyPress = millis();
    while (true) {
      char k = keypad.getKey();
      if (k) lastKeyPress = millis();
      if (millis() - lastKeyPress > 10000) {
        keluar = true;
        break;
      }
      if (!k) continue;
      if (k == '*') { keluar = true; break; }
      if (k == 'A') { cekSaldo(uid, millis()); break; }
      if (k == 'B') { prosesBayar(uid, millis()); break; }
      if (k == 'C') { prosesTopup(uid, millis()); break; }
    }
  }
}

// === CEK SALDO, BAYAR, TOPUP ===
void cekSaldo(const String& uid, unsigned long waktu_mulai) {
  HTTPClient http;
  String url = "http://192.168.137.1/sistem_paymen/api/cek_saldo.php?uid=" + uid;
  unsigned long t1 = millis();
  http.begin(url);
  http.GET();
  String res = http.getString();
  unsigned long t2 = millis();
  http.end();
  lcd.clear(); lcd.print("Saldo:"); lcd.setCursor(0,1); lcd.print(res);
  delay(2000);
  Serial.printf("[RESPON] Cek Saldo (Total): %lu ms\n", millis() - waktu_mulai);
  Serial.printf("[RESPON] Cek Saldo (Server Only): %lu ms\n", t2 - t1);
}

void prosesBayar(const String& uid, unsigned long waktu_mulai) {
  lcd.clear(); lcd.print("Masuk Nom:"); lcd.setCursor(0,1);
  String j = readInput(true, false);
  if (j.length() == 0) return;
  unsigned long t1 = millis();
  HTTPClient http;
  String url = "http://192.168.137.1/sistem_paymen/api/bayar.php?uid=" + uid + "&jumlah=" + j;
  http.begin(url);
  http.GET();
  String res = http.getString();
  unsigned long t2 = millis();
  http.end();
  lcd.clear(); lcd.print(res); delay(2000);
  if (res == "BERHASIL") {
    postForm("http://192.168.137.1/sistem_paymen/api/log_transaksi.php", "uid=" + uid + "&aksi=bayar&jumlah=" + j);
  }
  Serial.printf("[RESPON] Proses Bayar (Total): %lu ms\n", millis() - waktu_mulai);
  Serial.printf("[RESPON] Proses Bayar (Server Only): %lu ms\n", t2 - t1);
}

void prosesTopup(const String& uid, unsigned long waktu_mulai) {
  lcd.clear(); lcd.print("Nom TopUp:"); lcd.setCursor(0,1);
  String j = readInput(true, false);
  if (j.length() == 0) return;
  unsigned long t1 = millis();
  String res = postForm("http://192.168.137.1/sistem_paymen/api/topup.php", "uid=" + uid + "&jumlah=" + j);
  unsigned long t2 = millis();
  lcd.clear(); lcd.print(res); delay(2000);
  if (res == "BERHASIL") {
    postForm("http://192.168.137.1/sistem_paymen/api/log_transaksi.php", "uid=" + uid + "&aksi=topup&jumlah=" + j);
  }
  Serial.printf("[RESPON] Proses Topup (Total): %lu ms\n", millis() - waktu_mulai);
  Serial.printf("[RESPON] Proses Topup (Server Only): %lu ms\n", t2 - t1);
}
