import urllib.request
import re
import os
import time

os.makedirs('images/sangeet', exist_ok=True)

queries = [
    'sangeet+stage',
    'wedding+stage+decor',
    'wedding+dj+setup',
    'indian+wedding+decor',
    'wedding+mandap'
]

urls_to_download = []

for query in queries:
    url = f'https://www.flickr.com/search/?text={query}'
    print(f"Searching {url}...")
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
    try:
        html = urllib.request.urlopen(req).read().decode('utf-8')
        # match flickr large/medium sizes (e.g. _c.jpg or _b.jpg or _w.jpg)
        matches = re.findall(r'(\/\/live\.staticflickr\.com\/[^"]+_[zcw]\.jpg)', html)
        if not matches:
             matches = re.findall(r'(\/\/live\.staticflickr\.com\/[^"]+\.jpg)', html)
        
        urls_to_download.extend(["https:" + m for m in matches])
    except Exception as e:
        print(e)
    time.sleep(1)

# unique URLs
urls_to_download = list(set(urls_to_download))
print(f"Total unique images found: {len(urls_to_download)}")

saved_count = 0
for i, img_url in enumerate(urls_to_download[:100]):
    try:
        req_img = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
        img_data = urllib.request.urlopen(req_img).read()
        with open(f'images/sangeet/sangeet_{saved_count+1}.jpg', 'wb') as f:
            f.write(img_data)
        saved_count += 1
        if saved_count % 10 == 0:
            print(f"Progress: {saved_count}/100")
    except Exception as e:
         pass

print(f"Done! Downloaded {saved_count} distinct images for Sangeet.")
