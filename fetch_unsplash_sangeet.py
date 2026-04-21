import urllib.request
import json
import os

os.makedirs('images/sangeet', exist_ok=True)
urls_to_download = []

for page in range(1, 5):
    url = f'https://unsplash.com/napi/search/photos?query=wedding&per_page=30&page={page}'
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        response = urllib.request.urlopen(req).read()
        data = json.loads(response)
        for photo in data.get('results', []):
            img_url = photo['urls']['regular']
            urls_to_download.append(img_url)
    except Exception as e:
        print(f"Error on page {page}: {e}")

urls_to_download = list(set(urls_to_download))
print(f"Total unique wedding images from Unsplash: {len(urls_to_download)}")

saved_count = 0
for i, img_url in enumerate(urls_to_download[:100]):
    try:
        req_img = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
        img_data = urllib.request.urlopen(req_img).read()
        with open(f'images/sangeet/sangeet_{saved_count+1}.jpg', 'wb') as f:
            f.write(img_data)
        saved_count += 1
    except Exception as e:
         pass

print(f"Done! Downloaded {saved_count} distinct images.")
