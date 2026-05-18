import requests
from bs4 import BeautifulSoup
import json
from urllib.parse import urljoin
import sys

urls = {
    10: 'https://vietjack.com/toan-10-kn/ly-thuyet-toan-lop-10-ket-noi.jsp',
    11: 'https://vietjack.com/toan-11-kn/ly-thuyet-toan-lop-11-ket-noi.jsp',
    12: 'https://vietjack.com/toan-12-kn/ly-thuyet-toan-lop-12-ket-noi.jsp'
}

data = {}

for grade, url in urls.items():
    print(f"Fetching grade {grade}")
    res = requests.get(url)
    res.encoding = 'utf-8'
    soup = BeautifulSoup(res.text, 'html.parser')
    
    middle_col = soup.find('div', class_='middle-col')
    grade_data = []
    
    if middle_col:
        for a in middle_col.find_all('a'):
            href = a.get('href')
            if href and 'ly-thuyet' in href and 'bai' in href:
                # find previous <b> that contains "Chương"
                b_tag = a.find_previous('b')
                chapter_name = b_tag.get_text(strip=True) if b_tag and 'Chương' in b_tag.get_text() else 'Chương Mặc Định'
                title = a.get_text(strip=True)
                # Ignore duplicates
                if not any(d['link'] == urljoin(url, href) for d in grade_data):
                    grade_data.append({
                        'chapter': chapter_name,
                        'title': title,
                        'link': urljoin(url, href)
                    })
    data[grade] = grade_data

with open('vietjack_index.json', 'w', encoding='utf-8') as f:
    json.dump(data, f, ensure_ascii=False, indent=2)

print("Saved to vietjack_index.json")
