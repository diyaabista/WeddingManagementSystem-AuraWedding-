import urllib.request
import re

url = 'https://www.flickr.com/search/?text=wedding+stage+decor'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
try:
    html = urllib.request.urlopen(req).read().decode('utf-8')
    print("Fetched HTML length:", len(html))
    matches = re.findall(r'(\/\/live\.staticflickr\.com\/[^"]+\.jpg)', html)
    unique_matches = list(set(matches))
    print(f"Found {len(unique_matches)} unique images")
    for m in unique_matches[:5]:
        print("https:" + m)
except Exception as e:
    print(f"Error: {e}")
