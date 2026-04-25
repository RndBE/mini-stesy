import re

with open('app/Http/Controllers/SkemaIrigasiController.php', 'r', encoding='utf-8') as f:
    c = f.read()

nodes = re.findall(r\"'id'\s*=>\s*'([^']+)'(?:.*?)'type'\s*=>\s*'(?:junction|corner)'\", c)
existing = re.findall(r\"'([A-Za-z0-9_]+)'\s*=>\s*\[\s*'tma_hulu_cm'\", c)

unmapped = [n for n in nodes if n not in existing]

print('Missing:')
print('\n'.join(unmapped))
