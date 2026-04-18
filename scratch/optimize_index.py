import re

with open('assets/map-optimized.svg', 'r') as f:
    optimized_svg = f.read()

with open('index.html', 'r') as f:
    html = f.read()

# 1. Replace World Map SVG
# The current SVG starts around line 102: <svg class="w-full h-full object-contain opacity-20" viewBox="0 0 210 100" ...
# and ends with </svg>
map_start_marker = '<!-- WORLD MAP -->'
svg_pattern = r'<svg class="w-full h-full object-contain opacity-20" viewBox="0 0 210 100" xmlns="http://www.w3.org/2000/svg">.*?</svg>'
new_svg = optimized_svg.replace('<svg ', '<svg class="w-full h-full object-contain opacity-20" ')

html = re.sub(svg_pattern, new_svg, html, flags=re.DOTALL)

# 2. Update Service Images to JPEG and add Lazy Loading
# Patterns like src="images/services/svc-1.png" -> src="images/services/svc-1.jpeg"
# And add loading="lazy" decoding="async"
def optimize_img(match):
    img_tag = match.group(0)
    # Change png to jpeg for service images
    img_tag = re.sub(r'images/services/svc-(\d+)\.png', r'images/services/svc-\1.jpeg', img_tag)
    # Add lazy loading if not hero logo
    if 'assets/logo.png' not in img_tag and 'loading=' not in img_tag:
        img_tag = img_tag.replace('<img ', '<img loading="lazy" decoding="async" ')
    return img_tag

html = re.sub(r'<img [^>]+>', optimize_img, html)

with open('index.html', 'w') as f:
    f.write(html)

print("Optimization complete: Map inlined, images updated to JPEG, and lazy loading added.")
