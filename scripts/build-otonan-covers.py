"""Build public/images/otonan-*.svg, the opening cover illustrations, from the page ornaments.

Run after changing an ornament or parts/defs.blade.php: python scripts/build-otonan-covers.py
"""
import os
import re

os.chdir(os.path.join(os.path.dirname(os.path.abspath(__file__)), ".."))
base = "resources/views/invitations/templates/upacara-anak/"


def write(path, text):
    with open(path, "w", encoding="utf-8", newline="") as handle:
        handle.write(text)


def read(path):
    with open(path, encoding="utf-8") as handle:
        return handle.read()


def inner(text):
    """The markup between the outer <svg> tags."""
    return text[text.index(">", text.index("<svg")) + 1 : text.rindex("</svg>")]


defs = inner(read(base + "parts/defs.blade.php"))
defs = defs[defs.index("<defs>") + 6 : defs.rindex("</defs>")]


def body(name):
    return inner(read(base + "ornaments/" + name + ".blade.php"))


head = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 1000" preserveAspectRatio="xMidYMid slice"><defs>' + defs
jepun = '<g filter="url(#ub-soft)"><use href="#ub-jepun"/></g>'

prada = (
    '<pattern id="prada" width="56" height="56" patternUnits="userSpaceOnUse"><g fill="none" stroke="#c9a04e" stroke-opacity=".18">'
    '<path d="M28 14c6 6 6 22 0 28c-6-6-6-22 0-28zM14 28c6-6 22-6 28 0c-6 6-22 6-28 0z"/><circle r="5"/><circle cx="56" r="5"/><circle cy="56" r="5"/><circle cx="56" cy="56" r="5"/></g></pattern>'
)
write(
    "public/images/otonan-kori.svg",
    head + prada
    + '<radialGradient id="bg" cx=".5" cy=".28" r=".85"><stop offset="0" stop-color="#fffef9"/><stop offset=".6" stop-color="#fbf1dc"/><stop offset="1" stop-color="#eedbb2"/></radialGradient>'
    + '<linearGradient id="ground" x2="0" y2="1"><stop offset="0" stop-color="#e6d2a8" stop-opacity="0"/><stop offset="1" stop-color="#e0c996"/></linearGradient>'
    + '<g id="gate">' + body("gapura") + "</g></defs>"
    + '<rect width="600" height="1000" fill="url(#bg)"/><rect width="600" height="1000" fill="url(#prada)"/>'
    + '<rect y="900" width="600" height="100" fill="url(#ground)"/>'
    + '<use href="#gate" transform="translate(70 648) scale(1.1)"/><use href="#gate" transform="translate(530 648) scale(-1.1 1.1)"/>'
    + '<g transform="translate(500 120) rotate(18) scale(1.15)">' + jepun + "</g>"
    + '<g transform="translate(430 200) rotate(-26) scale(.62)">' + jepun + "</g>"
    + '<g transform="translate(104 136) rotate(44) scale(.86)">' + jepun + "</g>"
    + "</svg>",
)

write(
    "public/images/otonan-penjor.svg",
    head
    + '<linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#fbe3e8"/><stop offset="1" stop-color="#fff7f2"/></linearGradient>'
    + '<radialGradient id="bokeh"><stop offset="0" stop-color="#fff" stop-opacity=".85"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>'
    + '<radialGradient id="blush"><stop offset="0" stop-color="#f9c9d4" stop-opacity=".7"/><stop offset="1" stop-color="#f9c9d4" stop-opacity="0"/></radialGradient>'
    + '<g id="penjor">' + body("penjor") + "</g></defs>"
    + '<rect width="600" height="1000" fill="url(#bg)"/>'
    + '<circle cx="150" cy="240" r="120" fill="url(#bokeh)"/><circle cx="430" cy="560" r="170" fill="url(#blush)"/><circle cx="190" cy="760" r="140" fill="url(#bokeh)"/><circle cx="470" cy="150" r="90" fill="url(#bokeh)"/>'
    + '<use href="#penjor" transform="translate(262 40) scale(1.5)"/><use href="#penjor" transform="translate(338 40) scale(-1.5 1.5)"/>'
    + '<path d="M520 960V1000M80 960V1000" stroke="#b99a5e" stroke-width="8" stroke-linecap="round"/>'
    + '<g transform="translate(300 930) scale(.9)">' + jepun + "</g>"
    + '<g transform="translate(236 958) rotate(40) scale(.6)">' + jepun + "</g>"
    + '<g transform="translate(366 962) rotate(-30) scale(.66)">' + jepun + "</g>"
    + "</svg>",
)

wheel = '<g fill="none" stroke="#f8ebbd" stroke-width="1.1" opacity=".75"><circle r="97"/><circle r="82"/>'
for i in range(30):
    wheel += '<path transform="rotate(%d)" d="M0-82V-97"/>' % (i * 12)
wheel += '<circle r="68" stroke-dasharray="1.5 6"/></g><g fill="#f8ebbd" opacity=".75">'
for i in range(30):
    wheel += '<circle transform="rotate(%d)" cy="-89.5" r="%s"/>' % (i * 12 + 6, "3" if i % 5 == 0 else "1.6")
for i in range(7):
    wheel += '<circle transform="rotate(%.2f)" cy="-68" r="3.6"/>' % (i * 51.43)
wheel += "</g>"
moon = (
    '<circle r="110" fill="url(#ub-glow)"/>' + wheel + '<circle r="46" fill="url(#ub-moon)"/>'
    '<g filter="url(#ub-haze)" fill="#d9c684" opacity=".5"><ellipse cx="-15" cy="-11" rx="10" ry="7"/><ellipse cx="13" cy="9" rx="13" ry="8"/><ellipse cx="9" cy="-22" rx="5" ry="4"/><ellipse cx="-17" cy="17" rx="6" ry="4"/></g>'
)
poleng = (
    '<pattern id="poleng" width="40" height="40" patternUnits="userSpaceOnUse"><rect width="40" height="40" fill="#f8f5ee"/>'
    '<rect width="20" height="40" fill="#2c2c44" fill-opacity=".5"/><rect width="40" height="20" fill="#2c2c44" fill-opacity=".5"/></pattern>'
    '<linearGradient id="fold"><stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset=".18" stop-color="#fff" stop-opacity=".35"/>'
    '<stop offset=".42" stop-color="#000" stop-opacity=".12"/><stop offset=".68" stop-color="#fff" stop-opacity=".25"/><stop offset="1" stop-color="#000" stop-opacity=".1"/></linearGradient>'
)
stars = '<g fill="#fff" opacity=".8"><circle cx="120" cy="190" r="2"/><circle cx="470" cy="150" r="2.4"/><circle cx="520" cy="420" r="1.8"/><circle cx="90" cy="500" r="2"/><circle cx="440" cy="640" r="1.6"/><circle cx="170" cy="700" r="1.8"/></g>'
write(
    "public/images/otonan-purnama.svg",
    head + poleng
    + '<linearGradient id="bg" x2="0" y2="1"><stop offset="0" stop-color="#2f3069"/><stop offset=".45" stop-color="#4a4789"/><stop offset=".8" stop-color="#77669f"/><stop offset="1" stop-color="#9b80b3"/></linearGradient>'
    + '<g id="awan">' + body("awan") + "</g></defs>"
    + '<rect width="600" height="1000" fill="url(#bg)"/>' + stars
    + '<g transform="translate(300 236) scale(1.4)">' + moon + "</g>"
    + '<use href="#awan" transform="translate(40 262) scale(1.4)"/><use href="#awan" transform="translate(552 128) scale(-1.2 1.2)"/>'
    + '<use href="#awan" transform="translate(350 590) scale(1.1)" opacity=".6"/>'
    + '<rect width="600" height="56" fill="url(#poleng)"/><rect width="600" height="56" fill="url(#fold)"/>'
    + '<rect y="944" width="600" height="56" fill="url(#poleng)"/><rect y="944" width="600" height="56" fill="url(#fold)"/>'
    + "</svg>",
)

for name in ("kori", "penjor", "purnama"):
    text = read("public/images/otonan-%s.svg" % name)
    assert "{{" not in text and "@" not in text, name
print("ok")
