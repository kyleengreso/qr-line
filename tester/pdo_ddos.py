import threading
import requests
import time

url = "http://127.0.0.1:8080/tester/pdos.php"
total_requests = 2000
batch_size = 10

def fetch(i):
    try:
        response = requests.get(url, timeout=10)
        print(f"[{i}] {response.status_code} → {response.text.strip()}")
    except Exception as e:
        print(f"[{i}] Error: {e}")

for batch in range(0, total_requests, batch_size):
    threads = []
    for i in range(batch, batch + batch_size):
        t = threading.Thread(target=fetch, args=(i,))
        threads.append(t)
        t.start()
    for t in threads:
        t.join()
    print(f"Batch {batch // batch_size + 1} complete")
    time.sleep(1)

print("All threads completed.")
