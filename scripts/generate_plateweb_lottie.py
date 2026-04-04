#!/usr/bin/env python3

from __future__ import annotations

import json
from pathlib import Path

from lottie import objects
from lottie.exporters.svg import export_svg
from lottie.nvector import NVector
from lottie.objects.bezier import Bezier
from PIL import Image, ImageDraw


ROOT = Path("/Applications/XAMPP/xamppfiles/htdocs/cms")
OUTPUT_DIR = ROOT / "public" / "lotties"
ANIMATION_NAME = "plateweb-builder-platebook"
FRAMES = 240
FPS = 30
WIDTH = 1200
HEIGHT = 700


def rgb(hex_value: str) -> NVector:
    hex_value = hex_value.lstrip("#")
    return NVector(*(int(hex_value[i : i + 2], 16) / 255 for i in (0, 2, 4)))


WHITE = rgb("#FFFFFF")
BG = rgb("#F5F8FF")
BG_SOFT = rgb("#EAF1FF")
NAVY = rgb("#0F3D91")
BLUE = rgb("#2B63BE")
TEAL = rgb("#20C5C7")
GOLD = rgb("#F3C548")
TEXT = rgb("#1E293B")
TEXT_MUTED = rgb("#64748B")
STROKE = rgb("#D7E3FF")
CARD = rgb("#FCFDFF")


def add_rect(layer, size, color, radius, center=(0, 0), opacity=100, stroke=None, stroke_width=0):
    rect = objects.Rect(NVector(*center), NVector(*size), radius)
    layer.add_shape(rect)
    fill = objects.Fill(color)
    fill.opacity.value = opacity
    layer.add_shape(fill)
    if stroke is not None and stroke_width:
        stroke_obj = objects.Stroke(stroke, stroke_width)
        layer.add_shape(stroke_obj)


def add_circle(layer, diameter, color, center=(0, 0), opacity=100, stroke=None, stroke_width=0):
    ellipse = objects.Ellipse(NVector(*center), NVector(diameter, diameter))
    layer.add_shape(ellipse)
    fill = objects.Fill(color)
    fill.opacity.value = opacity
    layer.add_shape(fill)
    if stroke is not None and stroke_width:
        stroke_obj = objects.Stroke(stroke, stroke_width)
        layer.add_shape(stroke_obj)


def add_line(layer, points, color, width):
    bezier = Bezier()
    for point in points:
        bezier.add_point(NVector(*point))
    path = objects.Path(bezier)
    stroke = objects.Stroke(color, width)
    stroke.line_cap = objects.LineCap.Round
    stroke.line_join = objects.LineJoin.Round
    layer.add_shape(path)
    layer.add_shape(stroke)


def set_fade_in(layer, start, end, start_pos=None, end_pos=None, scale_from=None, scale_to=None):
    layer.transform.opacity.add_keyframe(start, 0)
    layer.transform.opacity.add_keyframe(end, 100)
    if start_pos is not None and end_pos is not None:
        layer.transform.position.add_keyframe(start, NVector(*start_pos))
        layer.transform.position.add_keyframe(end, NVector(*end_pos))
    if scale_from is not None and scale_to is not None:
        layer.transform.scale.add_keyframe(start, NVector(*scale_from))
        layer.transform.scale.add_keyframe(end, NVector(*scale_to))


def set_static(layer, pos):
    layer.transform.position.value = NVector(*pos)
    layer.transform.opacity.value = 100


def make_background(anim):
    layer = objects.ShapeLayer()
    anim.add_layer(layer)
    set_static(layer, (WIDTH / 2, HEIGHT / 2))
    add_rect(layer, (WIDTH, HEIGHT), BG, 0)

    blob_a = objects.ShapeLayer()
    anim.add_layer(blob_a)
    set_static(blob_a, (210, 120))
    add_circle(blob_a, 320, BLUE, opacity=14)
    blob_a.transform.scale.add_keyframe(0, NVector(100, 100))
    blob_a.transform.scale.add_keyframe(120, NVector(108, 108))
    blob_a.transform.scale.add_keyframe(240, NVector(100, 100))

    blob_b = objects.ShapeLayer()
    anim.add_layer(blob_b)
    set_static(blob_b, (980, 560))
    add_circle(blob_b, 380, TEAL, opacity=12)
    blob_b.transform.scale.add_keyframe(0, NVector(104, 104))
    blob_b.transform.scale.add_keyframe(120, NVector(96, 96))
    blob_b.transform.scale.add_keyframe(240, NVector(104, 104))

    blob_c = objects.ShapeLayer()
    anim.add_layer(blob_c)
    set_static(blob_c, (1020, 160))
    add_circle(blob_c, 220, GOLD, opacity=10)
    blob_c.transform.scale.add_keyframe(0, NVector(100, 100))
    blob_c.transform.scale.add_keyframe(120, NVector(110, 110))
    blob_c.transform.scale.add_keyframe(240, NVector(100, 100))


def make_browser_shell(anim):
    shell = objects.ShapeLayer()
    anim.add_layer(shell)
    set_fade_in(shell, 0, 20, (250, 345), (330, 345))
    add_rect(shell, (470, 332), CARD, 34, stroke=STROKE, stroke_width=6)

    toolbar = objects.ShapeLayer()
    anim.add_layer(toolbar)
    set_fade_in(toolbar, 0, 20, (250, 345), (330, 345))
    add_rect(toolbar, (470, 58), BG_SOFT, 34, center=(0, -137))

    for x, color, opacity in [(-176, NAVY, 20), (-148, BLUE, 26), (-120, TEAL, 32)]:
        dot = objects.ShapeLayer()
        anim.add_layer(dot)
        set_fade_in(dot, 4, 22, (250, 345), (330, 345))
        add_circle(dot, 16, color, center=(x, -137), opacity=opacity)

    search = objects.ShapeLayer()
    anim.add_layer(search)
    set_fade_in(search, 6, 24, (250, 345), (330, 345))
    add_rect(search, (114, 18), BLUE, 9, center=(118, -137), opacity=18)

    badge_bg = objects.ShapeLayer()
    anim.add_layer(badge_bg)
    set_fade_in(badge_bg, 10, 28, (160, 215), (182, 215), (84, 84), (100, 100))
    add_circle(badge_bg, 78, NAVY)

    badge_mark = objects.ShapeLayer()
    anim.add_layer(badge_mark)
    set_fade_in(badge_mark, 10, 28, (160, 215), (182, 215), (84, 84), (100, 100))
    add_line(badge_mark, [(-20, -2), (-10, 20), (2, -2), (12, 20), (22, -2)], WHITE, 10)


def make_browser_content(anim):
    hero_card = objects.ShapeLayer()
    anim.add_layer(hero_card)
    set_fade_in(hero_card, 20, 38, (320, 290), (330, 290))
    add_rect(hero_card, (356, 112), WHITE, 24, stroke=BG_SOFT, stroke_width=3)

    hero_gold = objects.ShapeLayer()
    anim.add_layer(hero_gold)
    set_fade_in(hero_gold, 24, 42, (320, 290), (330, 290))
    add_rect(hero_gold, (140, 18), GOLD, 9, center=(-82, -28), opacity=28)

    hero_text = objects.ShapeLayer()
    anim.add_layer(hero_text)
    set_fade_in(hero_text, 28, 46, (320, 290), (330, 290))
    add_rect(hero_text, (234, 22), TEXT, 11, center=(-28, 6), opacity=9)

    hero_meta = objects.ShapeLayer()
    anim.add_layer(hero_meta)
    set_fade_in(hero_meta, 32, 50, (320, 290), (330, 290))
    add_rect(hero_meta, (196, 16), TEXT_MUTED, 8, center=(-47, 40), opacity=12)

    left_card = objects.ShapeLayer()
    anim.add_layer(left_card)
    set_fade_in(left_card, 30, 48, (248, 392), (250, 392), (90, 90), (100, 100))
    add_rect(left_card, (146, 118), WHITE, 22, stroke=BG_SOFT, stroke_width=3)

    left_blue = objects.ShapeLayer()
    anim.add_layer(left_blue)
    set_fade_in(left_blue, 34, 52, (248, 392), (250, 392), (90, 90), (100, 100))
    add_rect(left_blue, (102, 18), BLUE, 9, center=(0, -24), opacity=18)

    left_muted = objects.ShapeLayer()
    anim.add_layer(left_muted)
    set_fade_in(left_muted, 38, 56, (248, 392), (250, 392), (90, 90), (100, 100))
    add_rect(left_muted, (78, 14), TEXT_MUTED, 7, center=(-12, 8), opacity=12)

    left_teal = objects.ShapeLayer()
    anim.add_layer(left_teal)
    set_fade_in(left_teal, 42, 60, (248, 392), (250, 392), (90, 90), (100, 100))
    add_rect(left_teal, (92, 14), TEAL, 7, center=(-4, 34), opacity=18)

    line_1 = objects.ShapeLayer()
    anim.add_layer(line_1)
    set_fade_in(line_1, 36, 52, (418, 365), (418, 365), (92, 92), (100, 100))
    add_rect(line_1, (150, 22), WHITE, 11, stroke=BG_SOFT, stroke_width=3)

    line_1_fill = objects.ShapeLayer()
    anim.add_layer(line_1_fill)
    set_fade_in(line_1_fill, 40, 56, (418, 365), (418, 365), (92, 92), (100, 100))
    add_rect(line_1_fill, (112, 10), TEXT, 5, opacity=10)

    line_2 = objects.ShapeLayer()
    anim.add_layer(line_2)
    set_fade_in(line_2, 44, 60, (414, 411), (414, 411), (92, 92), (100, 100))
    add_rect(line_2, (178, 22), WHITE, 11, stroke=BG_SOFT, stroke_width=3)

    line_2_fill = objects.ShapeLayer()
    anim.add_layer(line_2_fill)
    set_fade_in(line_2_fill, 48, 64, (414, 411), (414, 411), (92, 92), (100, 100))
    add_rect(line_2_fill, (132, 10), TEXT_MUTED, 5, opacity=11)

    line_3 = objects.ShapeLayer()
    anim.add_layer(line_3)
    set_fade_in(line_3, 52, 68, (394, 456), (394, 456), (92, 92), (100, 100))
    add_rect(line_3, (138, 22), WHITE, 11, stroke=BG_SOFT, stroke_width=3)

    line_3_fill = objects.ShapeLayer()
    anim.add_layer(line_3_fill)
    set_fade_in(line_3_fill, 56, 72, (394, 456), (394, 456), (92, 92), (100, 100))
    add_rect(line_3_fill, (86, 10), BLUE, 5, opacity=18)

    cta = objects.ShapeLayer()
    anim.add_layer(cta)
    set_fade_in(cta, 60, 76, (330, 504), (330, 504), (88, 88), (100, 100))
    add_rect(cta, (168, 34), NAVY, 17)


def make_connector(anim):
    dot_specs = [
        (0, 580, 642),
        (16, 560, 626),
        (32, 540, 608),
    ]
    for offset, start_x, end_x in dot_specs:
        dot = objects.ShapeLayer()
        anim.add_layer(dot)
        dot.transform.position.add_keyframe(76 + offset, NVector(start_x, 350))
        dot.transform.position.add_keyframe(140 + offset, NVector(end_x, 350))
        dot.transform.position.add_keyframe(204 + offset, NVector(start_x, 350))
        dot.transform.position.add_keyframe(240, NVector(start_x, 350))
        dot.transform.opacity.add_keyframe(76 + offset, 0)
        dot.transform.opacity.add_keyframe(88 + offset, 100)
        dot.transform.opacity.add_keyframe(140 + offset, 0)
        dot.transform.opacity.add_keyframe(204 + offset, 0)
        add_circle(dot, 18, GOLD)

    travel_card = objects.ShapeLayer()
    anim.add_layer(travel_card)
    travel_card.transform.position.add_keyframe(92, NVector(490, 344))
    travel_card.transform.position.add_keyframe(146, NVector(744, 324))
    travel_card.transform.position.add_keyframe(170, NVector(744, 324))
    travel_card.transform.scale.add_keyframe(92, NVector(76, 76))
    travel_card.transform.scale.add_keyframe(110, NVector(100, 100))
    travel_card.transform.scale.add_keyframe(146, NVector(100, 100))
    travel_card.transform.opacity.add_keyframe(92, 0)
    travel_card.transform.opacity.add_keyframe(102, 100)
    travel_card.transform.opacity.add_keyframe(150, 100)
    travel_card.transform.opacity.add_keyframe(168, 0)
    add_rect(travel_card, (88, 88), WHITE, 24, stroke=STROKE, stroke_width=4)

    for y, color, width in [(-18, BLUE, 40), (6, TEAL, 50), (28, GOLD, 34)]:
        travel_strip = objects.ShapeLayer()
        anim.add_layer(travel_strip)
        travel_strip.transform.position.add_keyframe(92, NVector(490, 344))
        travel_strip.transform.position.add_keyframe(146, NVector(744, 324))
        travel_strip.transform.position.add_keyframe(170, NVector(744, 324))
        travel_strip.transform.scale.add_keyframe(92, NVector(76, 76))
        travel_strip.transform.scale.add_keyframe(110, NVector(100, 100))
        travel_strip.transform.scale.add_keyframe(146, NVector(100, 100))
        travel_strip.transform.opacity.add_keyframe(92, 0)
        travel_strip.transform.opacity.add_keyframe(102, 100)
        travel_strip.transform.opacity.add_keyframe(150, 100)
        travel_strip.transform.opacity.add_keyframe(168, 0)
        add_rect(travel_strip, (width, 12 if y != -18 else 14), color, 6 if y != -18 else 7, center=(0, y), opacity=20 if color != GOLD else 22)


def make_phone_shell(anim):
    outer = objects.ShapeLayer()
    anim.add_layer(outer)
    set_fade_in(outer, 8, 28, (980, 350), (890, 350))
    add_rect(outer, (248, 448), TEXT, 62)
    add_rect(outer, (6, 48), TEXT, 3, center=(-131, -110), opacity=26)
    add_rect(outer, (96, 16), TEXT, 8, center=(0, -182))

    screen = objects.ShapeLayer()
    anim.add_layer(screen)
    set_fade_in(screen, 8, 28, (980, 350), (890, 350))
    add_rect(screen, (230, 430), CARD, 56, stroke=STROKE, stroke_width=4)
    add_rect(screen, (208, 376), BG, 42, center=(0, 18))

    badge_bg = objects.ShapeLayer()
    anim.add_layer(badge_bg)
    set_fade_in(badge_bg, 16, 34, (968, 212), (930, 212), (84, 84), (100, 100))
    add_circle(badge_bg, 72, BLUE)

    badge_rect = objects.ShapeLayer()
    anim.add_layer(badge_rect)
    set_fade_in(badge_rect, 16, 34, (968, 212), (930, 212), (84, 84), (100, 100))
    add_rect(badge_rect, (32, 26), WHITE, 6, center=(0, -4), opacity=100)

    badge_glyph = objects.ShapeLayer()
    anim.add_layer(badge_glyph)
    set_fade_in(badge_glyph, 18, 36, (968, 212), (930, 212), (84, 84), (100, 100))
    add_line(badge_glyph, [(-12, 8), (-3, 18), (16, 0)], WHITE, 7)


def make_phone_content(anim):
    top_pill = objects.ShapeLayer()
    anim.add_layer(top_pill)
    set_fade_in(top_pill, 96, 114, (890, 250), (890, 250), (92, 92), (100, 100))
    add_rect(top_pill, (132, 34), BG_SOFT, 17)
    add_rect(top_pill, (72, 10), BLUE, 5, opacity=18)

    cards = [
        (110, 128, 325, 170, TEAL, 0.16),
        (124, 142, 385, 182, BLUE, 0.14),
        (138, 156, 445, 194, GOLD, 0.2),
    ]
    for start, end, y, width, color, opacity in cards:
        card = objects.ShapeLayer()
        anim.add_layer(card)
        set_fade_in(card, start, end, (940, y), (890, y), (92, 92), (100, 100))
        add_rect(card, (width, 46), WHITE, 23, stroke=BG_SOFT, stroke_width=3)

        dot = objects.ShapeLayer()
        anim.add_layer(dot)
        set_fade_in(dot, start + 4, end + 4, (940, y), (890, y), (92, 92), (100, 100))
        add_circle(dot, 18, color, center=(-width / 2 + 28, 0), opacity=int(opacity * 100))

        copy = objects.ShapeLayer()
        anim.add_layer(copy)
        set_fade_in(copy, start + 8, end + 8, (940, y), (890, y), (92, 92), (100, 100))
        add_rect(copy, (width - 76, 12), TEXT, 6, center=(14, 0), opacity=10)

    calendar = objects.ShapeLayer()
    anim.add_layer(calendar)
    set_fade_in(calendar, 154, 176, (925, 532), (890, 532), (86, 86), (100, 100))
    add_rect(calendar, (164, 88), WHITE, 28, stroke=BG_SOFT, stroke_width=4)

    calendar_blue = objects.ShapeLayer()
    anim.add_layer(calendar_blue)
    set_fade_in(calendar_blue, 160, 182, (925, 532), (890, 532), (86, 86), (100, 100))
    add_rect(calendar_blue, (124, 18), BLUE, 9, center=(0, -22), opacity=22)

    calendar_copy = objects.ShapeLayer()
    anim.add_layer(calendar_copy)
    set_fade_in(calendar_copy, 164, 186, (925, 532), (890, 532), (86, 86), (100, 100))
    add_rect(calendar_copy, (110, 12), TEXT_MUTED, 6, center=(0, 6), opacity=10)

    calendar_teal = objects.ShapeLayer()
    anim.add_layer(calendar_teal)
    set_fade_in(calendar_teal, 168, 190, (925, 532), (890, 532), (86, 86), (100, 100))
    add_rect(calendar_teal, (82, 12), TEAL, 6, center=(-14, 28), opacity=20)

    check_bg = objects.ShapeLayer()
    anim.add_layer(check_bg)
    check_bg.transform.position.add_keyframe(170, NVector(948, 500))
    check_bg.transform.position.add_keyframe(240, NVector(948, 500))
    check_bg.transform.scale.add_keyframe(170, NVector(0, 0))
    check_bg.transform.scale.add_keyframe(186, NVector(118, 118))
    check_bg.transform.scale.add_keyframe(202, NVector(100, 100))
    check_bg.transform.scale.add_keyframe(224, NVector(108, 108))
    check_bg.transform.scale.add_keyframe(240, NVector(100, 100))
    check_bg.transform.opacity.add_keyframe(170, 0)
    check_bg.transform.opacity.add_keyframe(182, 100)
    add_circle(check_bg, 62, TEAL)

    check_mark = objects.ShapeLayer()
    anim.add_layer(check_mark)
    check_mark.transform.position.add_keyframe(170, NVector(948, 500))
    check_mark.transform.position.add_keyframe(240, NVector(948, 500))
    check_mark.transform.scale.add_keyframe(170, NVector(0, 0))
    check_mark.transform.scale.add_keyframe(186, NVector(118, 118))
    check_mark.transform.scale.add_keyframe(202, NVector(100, 100))
    check_mark.transform.scale.add_keyframe(224, NVector(108, 108))
    check_mark.transform.scale.add_keyframe(240, NVector(100, 100))
    check_mark.transform.opacity.add_keyframe(170, 0)
    check_mark.transform.opacity.add_keyframe(182, 100)
    add_line(check_mark, [(-14, 2), (-2, 16), (18, -10)], WHITE, 8)


def make_sparkles(anim):
    for x, y, delay in [(706, 258, 132), (748, 392, 150), (640, 438, 168)]:
        layer = objects.ShapeLayer()
        anim.add_layer(layer)
        layer.transform.position.add_keyframe(delay, NVector(x, y))
        layer.transform.position.add_keyframe(240, NVector(x, y))
        layer.transform.opacity.add_keyframe(delay, 0)
        layer.transform.opacity.add_keyframe(delay + 8, 100)
        layer.transform.opacity.add_keyframe(delay + 24, 0)
        add_line(layer, [(0, -10), (0, 10)], GOLD, 4)
        add_line(layer, [(-10, 0), (10, 0)], GOLD, 4)


def build_animation():
    anim = objects.Animation(FRAMES, FPS)
    anim.width = WIDTH
    anim.height = HEIGHT
    anim.name = "PlateWeb builder with Platebook"

    make_sparkles(anim)
    make_phone_content(anim)
    make_phone_shell(anim)
    make_connector(anim)
    make_browser_content(anim)
    make_browser_shell(anim)
    make_background(anim)

    return anim


def write_preview_html(json_name: str, html_path: Path):
    html_path.write_text(
        f"""<!doctype html>
<html lang="da">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PlateWeb Lottie Preview</title>
    <style>
      body {{
        margin: 0;
        min-height: 100vh;
        display: grid;
        place-items: center;
        background: linear-gradient(180deg, #eef4ff 0%, #f9fbff 100%);
        font-family: Inter, system-ui, sans-serif;
      }}
      .preview {{
        width: min(960px, 92vw);
        border-radius: 32px;
        background: rgba(255,255,255,0.75);
        border: 1px solid rgba(184, 204, 240, 0.9);
        box-shadow: 0 24px 60px rgba(35, 55, 110, 0.12);
        padding: 24px;
      }}
      #lottie {{
        width: 100%;
        aspect-ratio: 12 / 7;
      }}
      .caption {{
        margin: 12px 8px 0;
        color: #4a5671;
        font-size: 14px;
      }}
    </style>
  </head>
  <body>
    <div class="preview">
      <div id="lottie"></div>
      <p class="caption">PlateWeb builder + Platebook booking flow</p>
    </div>
    <script src="https://unpkg.com/lottie-web@5.12.2/build/player/lottie.min.js"></script>
    <script>
      lottie.loadAnimation({{
        container: document.getElementById('lottie'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: './{json_name}'
      }});
    </script>
  </body>
</html>
""",
        encoding="utf-8",
    )


def rgba(hex_value: str, alpha=255):
    hex_value = hex_value.lstrip("#")
    return tuple(int(hex_value[i : i + 2], 16) for i in (0, 2, 4)) + (alpha,)


def render_png_preview(path: Path):
    image = Image.new("RGBA", (WIDTH, HEIGHT), rgba("#F5F8FF"))
    draw = ImageDraw.Draw(image, "RGBA")

    draw.ellipse((20, -40, 340, 280), fill=rgba("#2B63BE", 30))
    draw.ellipse((780, 360, 1160, 740), fill=rgba("#20C5C7", 26))
    draw.ellipse((900, 20, 1120, 240), fill=rgba("#F3C548", 22))

    # Browser shell
    draw.rounded_rectangle((96, 180, 566, 512), radius=34, fill=rgba("#FCFDFF"), outline=rgba("#D7E3FF"), width=6)
    draw.rounded_rectangle((96, 180, 566, 238), radius=34, fill=rgba("#EAF1FF"))
    draw.ellipse((146, 208, 162, 224), fill=rgba("#0F3D91", 60))
    draw.ellipse((174, 208, 190, 224), fill=rgba("#2B63BE", 72))
    draw.ellipse((202, 208, 218, 224), fill=rgba("#20C5C7", 82))
    draw.rounded_rectangle((392, 207, 506, 225), radius=9, fill=rgba("#2B63BE", 48))

    # Browser badge
    draw.ellipse((144, 176, 222, 254), fill=rgba("#0F3D91"))
    draw.line(((162, 211), (172, 232), (184, 211), (194, 232), (204, 211)), fill=rgba("#FFFFFF"), width=10, joint="curve")

    # Browser content
    draw.rounded_rectangle((152, 234, 508, 346), radius=24, fill=rgba("#FFFFFF"), outline=rgba("#EAF1FF"), width=3)
    draw.rounded_rectangle((178, 252, 318, 270), radius=9, fill=rgba("#F3C548", 80))
    draw.rounded_rectangle((178, 284, 412, 306), radius=11, fill=rgba("#1E293B", 26))
    draw.rounded_rectangle((178, 318, 374, 334), radius=8, fill=rgba("#64748B", 30))

    draw.rounded_rectangle((176, 334, 322, 452), radius=22, fill=rgba("#FFFFFF"), outline=rgba("#EAF1FF"), width=3)
    draw.rounded_rectangle((198, 352, 300, 370), radius=9, fill=rgba("#2B63BE", 52))
    draw.rounded_rectangle((198, 384, 276, 398), radius=7, fill=rgba("#64748B", 34))
    draw.rounded_rectangle((198, 410, 290, 424), radius=7, fill=rgba("#20C5C7", 56))

    for left, top, width_box, inner_width, inner_color in [
        (324, 354, 150, 112, rgba("#1E293B", 26)),
        (320, 400, 178, 132, rgba("#64748B", 28)),
        (300, 445, 138, 86, rgba("#2B63BE", 56)),
    ]:
        draw.rounded_rectangle((left, top, left + width_box, top + 22), radius=11, fill=rgba("#FFFFFF"), outline=rgba("#EAF1FF"), width=3)
        draw.rounded_rectangle((left + 19, top + 6, left + 19 + inner_width, top + 16), radius=5, fill=inner_color)

    draw.rounded_rectangle((246, 487, 414, 521), radius=17, fill=rgba("#0F3D91"))

    # Connector
    for x in (574, 608, 642):
        draw.ellipse((x, 340, x + 18, 358), fill=rgba("#F3C548"))
    draw.rounded_rectangle((476, 304, 564, 392), radius=24, fill=rgba("#FFFFFF"), outline=rgba("#D7E3FF"), width=4)
    draw.rounded_rectangle((500, 319, 540, 333), radius=7, fill=rgba("#2B63BE", 54))
    draw.rounded_rectangle((494, 343, 544, 355), radius=6, fill=rgba("#20C5C7", 52))
    draw.rounded_rectangle((502, 365, 536, 377), radius=6, fill=rgba("#F3C548", 56))

    # Phone shell
    draw.rounded_rectangle((764, 126, 1012, 574), radius=62, fill=rgba("#1E293B"))
    draw.rounded_rectangle((773, 135, 1003, 565), radius=56, fill=rgba("#FCFDFF"), outline=rgba("#D7E3FF"), width=4)
    draw.rounded_rectangle((854, 160, 950, 176), radius=8, fill=rgba("#1E293B"))
    draw.rounded_rectangle((786, 170, 994, 546), radius=42, fill=rgba("#F5F8FF"))
    draw.rounded_rectangle((762, 216, 768, 264), radius=3, fill=rgba("#1E293B", 70))

    # Phone badge
    draw.ellipse((893, 176, 965, 248), fill=rgba("#2B63BE"))
    draw.rounded_rectangle((913, 192, 945, 218), radius=6, fill=rgba("#FFFFFF"))
    draw.line(((909, 220), (920, 231), (938, 213)), fill=rgba("#FFFFFF"), width=7, joint="curve")

    draw.rounded_rectangle((824, 233, 956, 267), radius=17, fill=rgba("#EAF1FF"))
    draw.rounded_rectangle((854, 245, 926, 255), radius=5, fill=rgba("#2B63BE", 44))

    step_specs = [
        (805, 302, 975, 348, "#20C5C7", 44, 95),
        (799, 362, 981, 408, "#2B63BE", 38, 106),
        (793, 422, 987, 468, "#F3C548", 52, 118),
    ]
    for left, top, right, bottom, color, alpha, bar_width in step_specs:
        draw.rounded_rectangle((left, top, right, bottom), radius=23, fill=rgba("#FFFFFF"), outline=rgba("#EAF1FF"), width=3)
        draw.ellipse((left + 19, top + 14, left + 37, top + 32), fill=rgba(color, alpha))
        draw.rounded_rectangle((left + 52, top + 17, left + 52 + bar_width, top + 29), radius=6, fill=rgba("#1E293B", 26))

    draw.rounded_rectangle((808, 488, 972, 576), radius=28, fill=rgba("#FFFFFF"), outline=rgba("#EAF1FF"), width=4)
    draw.rounded_rectangle((828, 504, 952, 522), radius=9, fill=rgba("#2B63BE", 56))
    draw.rounded_rectangle((835, 532, 945, 544), radius=6, fill=rgba("#64748B", 30))
    draw.rounded_rectangle((821, 554, 903, 566), radius=6, fill=rgba("#20C5C7", 52))

    draw.ellipse((917, 469, 979, 531), fill=rgba("#20C5C7"))
    draw.line(((934, 500), (946, 514), (966, 488)), fill=rgba("#FFFFFF"), width=8, joint="curve")

    # Sparkles
    for x, y in [(706, 258), (748, 392), (640, 438)]:
        draw.line(((x, y - 10), (x, y + 10)), fill=rgba("#F3C548"), width=4)
        draw.line(((x - 10, y), (x + 10, y)), fill=rgba("#F3C548"), width=4)

    image.save(path)


def main():
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    animation = build_animation()

    json_path = OUTPUT_DIR / f"{ANIMATION_NAME}.json"
    svg_path = OUTPUT_DIR / f"{ANIMATION_NAME}-preview.svg"
    html_path = OUTPUT_DIR / f"{ANIMATION_NAME}-preview.html"
    png_path = OUTPUT_DIR / f"{ANIMATION_NAME}-preview.png"

    json_path.write_text(json.dumps(animation.to_dict(), indent=2), encoding="utf-8")
    export_svg(animation, str(svg_path), frame=188)
    write_preview_html(json_path.name, html_path)
    render_png_preview(png_path)

    print(f"Wrote {json_path}")
    print(f"Wrote {svg_path}")
    print(f"Wrote {html_path}")
    print(f"Wrote {png_path}")


if __name__ == "__main__":
    main()
