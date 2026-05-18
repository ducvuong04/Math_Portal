import mysql.connector
from bs4 import BeautifulSoup
import os
import re

# Database connection
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="math_portal_12"
)
cursor = db.cursor(buffered=True)

# Image dir
img_dir = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'assets', 'images', 'theory')

# Promotional image filenames to delete and clean up in HTML
promo_images = [
    'logo.png', 'apple_store.jpg', 'google_play.jpg', 
    'trac-nghiem.png', 'de-thi.png', 'bai-tap.png', 
    'de-hsg.png', 'giao-an-pp.png', 'giao-an-word.png'
]

# 1. Delete physical files from disk
print("1. Cleaning up local promotional image files...")
for img in promo_images:
    path = os.path.join(img_dir, img)
    if os.path.exists(path):
        try:
            os.remove(path)
            print(f"   Removed: {img}")
        except Exception as e:
            print(f"   Error removing {img}: {e}")
    else:
        print(f"   Already removed or not found: {img}")

# 2. Update DB records
print("\n2. Cleaning up DB topics...")
cursor.execute("SELECT id, title, theory FROM topics")
rows = cursor.fetchall()

cleaned_count = 0

for row in rows:
    topic_id, title, theory_html = row
    if not theory_html:
        continue
        
    soup = BeautifulSoup(theory_html, 'html.parser')
    modified = False
    
    # Clean up specific elements
    
    # a. Decompose sharing, paging, app download links and promotional sections
    # Find all elements with classes containing paging, social, fb, box-new, etc.
    classes_to_remove = [
        'paging', 'social-btn', 'fb-like', 'fb-share-button', 
        'box-new-title', 'box-new-new', 'quang-cao', 'QC'
    ]
    for cls in classes_to_remove:
        for tag in soup.find_all(class_=re.compile(cls, re.IGNORECASE)):
            tag.decompose()
            modified = True
            
    # b. Decompose download_appnew ID elements
    for tag in soup.find_all(id='download_appnew'):
        tag.decompose()
        modified = True
        
    # c. Remove embed / iframe / video tags to prevent copyright video violations
    for tag in soup.find_all(['iframe', 'video', 'embed', 'object']):
        tag.decompose()
        modified = True
        
    # d. Decompose images that are promotional/logo and fix incorrect math_portal_12 base paths
    for img in soup.find_all('img'):
        src = img.get('src', '')
        # Check if any promo image is in the src
        if any(promo in src for promo in promo_images) or 'vietjack' in src.lower() or 'logo' in src.lower():
            img.decompose()
            modified = True
        elif '/math_portal_12/assets/' in src:
            # Fix the 404 path mismatch from /math_portal_12/ to /bài thực hành 4.2/
            new_src = src.replace('/math_portal_12/assets/', '/bài thực hành 4.2/assets/')
            img['src'] = new_src
            modified = True
            
    # e. Remove any links pointing to VietJack
    for a in soup.find_all('a'):
        href = a.get('href', '')
        if 'vietjack.com' in href.lower() or 'shopee.vn' in href.lower() or 'lazada.vn' in href.lower():
            # Replace link with text content to preserve readability if inside text, otherwise decompose
            if len(a.get_text()) > 0:
                a.replace_with(a.get_text())
            else:
                a.decompose()
            modified = True
            
    # f. Search and clean up specific text containing "Vietjack" or "VietJack" or plain-text placeholders
    # We will search for all text elements containing Vietjack or ads keywords
    for element in soup.find_all(string=re.compile(r'vietjack|quảng cáo|khóa học.*199k|199k', re.IGNORECASE)):
        parent = element.parent
        if parent:
            text = element.string
            # Check if it's purely promotional, e.g. "Sale 40% sách Toán Vietjack", "Xem Khóa học Toán 10 KNTT (199k)", "Quảng cáo"
            if any(kw in text.lower() for kw in ['sale', 'shopee', 'tải app', 'app store', 'google play', 'quảng cáo', '199k', 'khóa học']):
                # Decompose the parent to clean it completely
                parent.decompose()
            else:
                # Just replace "Vietjack" or "VietJack" with neutral "MathPortal"
                new_text = re.sub(r'vietjack', 'MathPortal', text, flags=re.IGNORECASE)
                element.replace_with(new_text)
            modified = True

    # g. Clean up outer containers if beautifulsoup wrapped or if we have empty paragraphs/lists left
    # Remove empty divs, paragraphs, lists, list items that might be left after decomposing
    for tag in soup.find_all(['p', 'div', 'li', 'ul', 'ol', 'font', 'span']):
        if tag.get_text(strip=True) == '' and not tag.find_all('img'):
            tag.decompose()
            modified = True
            
    if modified:
        cleaned_html = str(soup)
        cursor.execute("UPDATE topics SET theory = %s WHERE id = %s", (cleaned_html, topic_id))
        cleaned_count += 1

db.commit()
print(f"\nSuccessfully cleaned, updated image paths, and removed placeholders for {cleaned_count} topics in the database!")
print("Clean-up finished completely!")
