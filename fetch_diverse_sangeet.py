import urllib.request
import os
import time

os.makedirs('images/sangeet', exist_ok=True)
saved_count = 0

keywords = [
    'sangeet,stage',
    'dj,setup',
    'dance,floor,wedding',
    'event,decor',
    'indian,wedding,stage',
    'party,lights',
    'wedding,reception',
    'banquet,hall',
    'concert,stage',
    'wedding,lighting'
]

urls = []
for keyword in keywords:
    for i in range(1, 11):
         urls.append(f"https://loremflickr.com/600/400/{keyword}/all?lock={i}")

for i, img_url in enumerate(urls):
    try:
        req_img = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
        img_data = urllib.request.urlopen(req_img).read()
        with open(f'images/sangeet/sangeet_{i+1}.jpg', 'wb') as f:
            f.write(img_data)
        saved_count += 1
        if saved_count % 10 == 0:
            print(f"Progress: {saved_count}/100")
    except Exception as e:
        print(f"Failed {img_url}: {e}")
    time.sleep(0.1)
        
print(f"Successfully saved {saved_count} diverse images.")
