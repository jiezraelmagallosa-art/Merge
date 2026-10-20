import base64
from PIL import Image
import io

img = Image.open('mobile_app/assets/images/cit_logo.png')
img = img.resize((800, 800), Image.Resampling.LANCZOS)
buf = io.BytesIO()
img.save(buf, format='PNG', optimize=True)
b64_str = base64.b64encode(buf.getvalue()).decode('utf-8')

svg_path = 'mobile_app/assets/images/attendee_app_icon.svg'
with open(svg_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = 'xlink:href="cit_logo.png" href="cit_logo.png"'
replacement = f'xlink:href="data:image/png;base64,{b64_str}" href="data:image/png;base64,{b64_str}"'
content = content.replace(target, replacement)

with open(svg_path, 'w', encoding='utf-8') as f:
    f.write(content)

print('Updated attendee_app_icon.svg with embedded base64 official logo!')
