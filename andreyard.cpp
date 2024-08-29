    #include <SoftwareSerial.h>
    #include <AES.h>
    #include <AESLib.h>
    #include <AES_config.h>
    #include <base64.h>
    #include <ESP8266WiFi.h>
    #include <ESP8266HTTPClient.h>
    #include <ArduinoJson.h>

    #include <Arduino.h>

    // Define RX and TX pins for communication with NodeMCU
    SoftwareSerial NodeMCU(D2, D3); // RX, TX

    // Variables to store sensor data
    float phValue = 7.00;
    float temperature =26.12;
    float waterLevel =20.1;

        String ipAddress = WiFi.localIP().toString();
        String macAddress = WiFi.macAddress();

        String key_str = "ISUHydroSec2024!";
        String iv_str = "HydroVertical143";

    String towercode="2024";
        String encryptedPhValue;
        String encryptedTemp ;
        String encryptedWaterLevel ;
        String encryptedKey ;
        String encryptedIv;

    // WiFi settings
    const char* ssid = "me";
    const char* wifi_password = "12345678";

    // PHP script URL
    String URL = "http://192.168.53.1:8000/api/iba";
    //String URL = "http://192.168.53.3/hydro/test_data.php";

    AES aes;
    byte cipher[1000];
    char b64[1000];

    // Function to encrypt a message
    String do_encrypt(String msg, String key_str, String iv_str) {
        byte iv[16];
        memcpy(iv, (byte *)iv_str.c_str(), 16);

        int blen = base64_encode(b64, (char *)msg.c_str(), msg.length());

        aes.calc_size_n_pad(blen);
        int len = aes.get_size();
        byte plain_p[len];
        for (int i = 0; i < blen; ++i) plain_p[i] = b64[i];
        for (int i = blen; i < len; ++i) plain_p[i] = '\0';

        int blocks = len / 16;
        aes.set_key((byte *)key_str.c_str(), 16);
        aes.cbc_encrypt(plain_p, cipher, blocks, iv);

        base64_encode(b64, (char *)cipher, len);
        String encrypted_data = String((char *)b64);
        return encrypted_data;
    }

    // Function to decrypt a message
    String do_decrypt(String encrypted_msg, String key_str, String iv_str) {
        byte iv[16];
        memcpy(iv, (byte *)iv_str.c_str(), 16);

        int cipher_len = base64_decode((char *)cipher, (char *)encrypted_msg.c_str(), encrypted_msg.length());

        aes.calc_size_n_pad(cipher_len);
        int len = aes.get_size();
        byte plain_p[len];
        int blocks = len / 16;
        aes.set_key((byte *)key_str.c_str(), 16);
        aes.cbc_decrypt(cipher, plain_p, blocks, iv);

        for (int i = 0; i < len; ++i) {
            if (plain_p[i] == '\0') {
                plain_p[i] = ' ';
            }
        }

        base64_decode(b64, (char *)plain_p, len);
        String decrypted_data = String((char *)b64);
        return decrypted_data;
    }

    void setup() {
        // Initialize the Serial Monitor
        Serial.begin(9600);

        // Initialize SoftwareSerial for communication with NodeMCU
        NodeMCU.begin(4800); // Set baud rate for communication

        Serial.println();

        WiFi.begin(ssid, wifi_password);
        while (WiFi.status() != WL_CONNECTED) {
            delay(500);
            Serial.print(".");
        }
        Serial.println("WiFi connected");
    }

    void connectWiFi() {
        WiFi.begin(ssid, wifi_password);
        while (WiFi.status() != WL_CONNECTED) {
            delay(500);
            Serial.print(".");
        }
        Serial.println("WiFi connected");
    }

    void loop() {
        if (WiFi.status() != WL_CONNECTED) {
            connectWiFi();
        }
        // Check if data is available from NodeMCU
        while (NodeMCU.available() > 0) {
            // Read the incoming data as a string until a newline character
            String receivedData = NodeMCU.readStringUntil('\n');
            
            // Print the received data to the Serial Monitor
            Serial.print("Received data: ");
            Serial.println(receivedData);

            // Check if the data corresponds to pH value
            if (receivedData.startsWith("PH:")) {
                // Extract and convert the pH value from the string
                phValue = receivedData.substring(3).toFloat();
                Serial.print("Received pH Value: ");
                Serial.println(phValue, 2); // Print with 2 decimal places
            } 
            // Check if the data corresponds to temperature
            else if (receivedData.startsWith("TEMP:")) {
                // Extract and convert the temperature from the string
                temperature = receivedData.substring(5).toFloat();
                Serial.print("Received Temperature: ");
                Serial.println(temperature, 2); // Print with 2 decimal places
            } 
            // Check if the data corresponds to nutrient level (water level)
            else if (receivedData.startsWith("NutrientLevel:")) {
                // Extract and convert the nutrient level from the string
                waterLevel = receivedData.substring(14).toFloat(); // Adjusted to correctly extract water level
                Serial.print("Received NutrientLevel: ");
                Serial.println(waterLevel, 2); // Print with 2 decimal places
            }
        }

        // Optional: Send data back to Arduino or other device
        // Example of sending a value back
        // float someValue = 123.45; // Example float value
        // NodeMCU.print("VALUE:");
        // NodeMCU.println(someValue, 2); // Send float with 2 decimal places
    //fetchData();
    EncryptData();
        delay(5000); // Adjust the delay as needed
    }

    void EncryptData() {

        // Generate two new random 16-character strings
    String key = generateRandomString(16);
    String iv = generateRandomString(16);

    String phValueStr = String(phValue, 2);
    String temperatureStr = String(temperature, 2);
    String waterLevelStr = String(waterLevel, 2);


    // Print the newly generated strings to the Serial Monitor
    Serial.println("New Generated String 1: " + key);
    Serial.println("New Generated String 2: " + iv);

        encryptedPhValue = do_encrypt(phValueStr, key, iv);
        encryptedTemp = do_encrypt(temperatureStr, key, iv);
        encryptedWaterLevel = do_encrypt(waterLevelStr, key, iv);

        encryptedKey = do_encrypt(key, key_str, iv_str);
        encryptedIv = do_encrypt(iv, key_str, iv_str);

        String encryptedIp = do_encrypt(ipAddress, key_str, iv_str);
        String encryptedMac = do_encrypt(iv, key_str, iv_str);
        String encryptedtowercode = do_encrypt(towercode, key_str, iv_str);


        Serial.print("Original pH Value: ");
        Serial.println(phValue);
        Serial.print("Encrypted pH Value: ");
        Serial.println(encryptedPhValue);

        Serial.print("Original Temperature: ");
        Serial.println(temperature);
        Serial.print("Encrypted Temperature: "); 
        Serial.println(encryptedTemp);

        Serial.print("Original Water Level: ");
        Serial.println(waterLevel);
        Serial.print("Encrypted Water Level: ");
        Serial.println(encryptedWaterLevel);

    sendDataToServer(encryptedPhValue, encryptedTemp, encryptedWaterLevel, encryptedKey, encryptedIv, encryptedIp, encryptedMac,encryptedtowercode);
    }

    String generateRandomString(int length) {
    String chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    String randomString = "";

    for (int i = 0; i < length; i++) {
        int randomIndex = random(0, chars.length());
        randomString += chars[randomIndex];
    }

    return randomString;
    }


    void DecryptData() {
        String key_str = "ISUHydroSec2024!";
        String iv_str = "HydroVertical143";

        String keyy = do_decrypt(encryptedKey, key_str, iv_str);
        String ivv = do_decrypt(encryptedIv, key_str, iv_str);

        String decryptedPhValue = do_decrypt(encryptedPhValue, keyy, ivv);
        String decryptedTemp = do_decrypt(encryptedTemp, keyy, ivv);
        String decryptedWaterLevel = do_decrypt(encryptedWaterLevel, keyy, ivv);

        Serial.print("Decrypted pH Value: ");
        Serial.println(decryptedPhValue);

        Serial.print("Decrypted Temperature: ");
        Serial.println(decryptedTemp);

        Serial.print("Decrypted Water Level: ");
        Serial.println(decryptedWaterLevel);
    }


    // void sendDataToServer(String encryptedPhValue, String encryptedTemp, String encryptedWaterLevel, String encryptedKey, String encryptedIv) {
    //     WiFiClient client;
    //     HTTPClient http;
    //     http.begin(client, URL);
    //     http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    //     String postData = "phValue=" + urlencode(encryptedPhValue) + "&temp=" + urlencode(encryptedTemp) + "&waterLevel=" + urlencode(encryptedWaterLevel) + "&key=" + urlencode(encryptedKey) + "&iv=" + urlencode(encryptedIv);
    //     int httpCode = http.POST(postData);
    //     String payload = http.getString();

    //     Serial.print("URL: ");
    //     Serial.println(URL);
    //     Serial.print("Data: ");
    //     Serial.println(postData);
    //     Serial.print("httpCode: ");
    //     Serial.println(httpCode);
    //     Serial.print("payload: ");
    //     Serial.println(payload);
    //     Serial.println("--------------------------------------------------");

    //     http.end();
    // }

    void sendDataToServer(String encryptedPhValue, String encryptedTemp, String encryptedWaterLevel, 
    String encryptedKey, String encryptedIv,String encryptedIp,String encryptedMac, String encryptedtowercode ) {
        WiFiClient client;
        HTTPClient http;
        http.begin(client, URL);
        http.addHeader("Content-Type", "application/json");  // Set content type to JSON

        StaticJsonDocument<300> doc;
        // Add data to JSON object
        doc["phValue"] = encryptedPhValue;
        doc["temp"] = encryptedTemp;
        doc["waterLevel"] = encryptedWaterLevel;
        doc["key"] = encryptedKey;
        doc["iv"] = encryptedIv;
        doc["ipAddress"] = encryptedIp; 
        doc["macAddress"] = encryptedMac;
        doc["towercode"] = encryptedtowercode; 
    


        // Serialize JSON object to string
        String postData;
        serializeJson(doc, postData);

        // Send the JSON object as the POST body
        int httpCode = http.POST(postData);
        String payload = http.getString();

        // Debugging output
        Serial.print("URL: ");
        Serial.println(URL);
        Serial.print("Data: ");
        Serial.println(postData);
        Serial.print("httpCode: ");
        Serial.println(httpCode);
        Serial.print("payload: ");
        Serial.println(payload);
        Serial.println("--------------------------------------------------");

        http.end();
    }


    void fetchData() {
        if (WiFi.status() == WL_CONNECTED) {
            WiFiClient client;
            HTTPClient http;

            http.begin(client, "http://192.168.0.101/hydro/pass_esp.php"); // Replace with your actual URL
            int httpResponseCode = http.GET();

            if (httpResponseCode > 0) {
                String payload = http.getString();
                Serial.println("HTTP Response code: " + String(httpResponseCode));
                Serial.println("Payload: " + payload);

                // Parse JSON
                DynamicJsonDocument doc(1024);
                deserializeJson(doc, payload);

                if (doc.containsKey("error")) {
                    Serial.println("Error: " + String(doc["error"].as<const char*>()));
                } else {
                    String encryptedPh = doc["ph"];
                    String encryptedTemp = doc["temp"];
                    String encryptedWaterLevel = doc["waterlevel"];
                    String iv = doc["iv"]; // Ensure the IV used is the same for decryption
                    String key = doc["key"]; // Ensure the IV used is the same for decryption

                    // Decrypt the data
                    String decryptedIv = do_decrypt(iv, key_str, iv_str);
                    String decryptedKey = do_decrypt(key, key_str, iv_str);
                    String decryptedPh = do_decrypt(encryptedPh, decryptedKey, decryptedIv);
                    String decryptedTemp = do_decrypt(encryptedTemp, decryptedKey, decryptedIv);
                    String decryptedWaterLevel = do_decrypt(encryptedWaterLevel, decryptedKey, decryptedIv);

                    Serial.println("Decrypted pH: " + decryptedPh);
                    Serial.println("Decrypted Temperature: " + decryptedTemp);
                    Serial.println("Decrypted Water Level: " + decryptedWaterLevel);
                }
            } else {
                Serial.println("Error on HTTP request");
            }

            http.end();
        } else {
            Serial.println("WiFi Disconnected");
        }
    }

    String urlencode(String str) {
        String encodedString = "";
        char c;
        char code0;
        char code1;
        char code2;
        for (int i = 0; i < str.length(); i++) {
            c = str.charAt(i);
            if (c == ' ') {
                encodedString += '+';
            } else if (isalnum(c)) {
                encodedString += c;
            } else {
                code1 = (c & 0xf) + '0';
                if ((c & 0xf) > 9) {
                    code1 = (c & 0xf) - 10 + 'A';
                }
                c = (c >> 4) & 0xf;
                code0 = c + '0';
                if (c > 9) {
                    code0 = c - 10 + 'A';
                }
                code2 = '\0';
                encodedString += '%';
                encodedString += code0;
                encodedString += code1;
            }
            yield();
        }
        return encodedString;
    }