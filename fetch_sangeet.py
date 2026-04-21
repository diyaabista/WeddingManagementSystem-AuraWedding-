import urllib.request
import os

os.makedirs('images/sangeet', exist_ok=True)
saved_count = 0

# Randomizing the URLs slightly to ensure uniqueness from loremflickr
urls = [f'https://loremflickr.com/600/400/wedding,stage,decoration/all?lock={i}' for i in range(1, 101)]

for i, img_url in enumerate(urls):
    print(f"Downloading {i+1}...")
    try:
        req_img = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
        img_data = urllib.request.urlopen(req_img).read()
        with open(f'images/sangeet/sangeet_{i+1}.jpg', 'wb') as f:
            f.write(img_data)
        saved_count += 1
    except Exception as e:
        print(f"Failed to fetch {img_url}: {e}")
        
print(f"Successfully saved {saved_count} images.")
