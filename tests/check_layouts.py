"""Non-production visual/static regression checks.
Run from this directory: python check_layouts.py
Requires Python playwright and locally installed Chromium.
Does not simulate or replace the original plugin's Load More JavaScript.
"""
from pathlib import Path
from playwright.sync_api import sync_playwright

html = Path(__file__).with_name('visual-fixture.html').read_text(encoding='utf-8')
css = (Path(__file__).parent.parent / 'assets' / 'layouts.css').read_text(encoding='utf-8')
html = html.replace('<link rel="stylesheet" href="../assets/layouts.css">', '<style>' + css + '</style>')

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True, executable_path='/usr/bin/chromium', args=['--no-sandbox'])
    try:
        for width, height, label in [(1440, 900, 'desktop'), (800, 900, 'tablet'), (390, 844, 'mobile')]:
            page = browser.new_page(viewport={'width': width, 'height': height}, device_scale_factor=1)
            page.set_content(html)
            assert page.locator('.elementor-widget').count() == 4
            assert page.locator('#dental-content').inner_text().find('سعادت آباد تهران') >= 0
            assert page.locator('#industrial-content').inner_text().find('ادامه متن فارسی طولانی') >= 0
            for card_id in ('card-dental', 'card-beauty', 'card-industrial', 'card-dental-2'):
                root = page.locator('#' + card_id)
                assert root.locator('button').count() == 1
                assert root.locator('button').get_attribute('aria-expanded') == 'false'
                assert root.locator('.elementor-widget-container').evaluate("el => getComputedStyle(el).direction") == 'rtl'
                assert root.locator('.elementor-widget-container').evaluate("el => getComputedStyle(el, '::after').opacity") == '1'
            assert page.evaluate('document.documentElement.scrollWidth <= innerWidth + 1'), f'horizontal overflow at {width}'
            if width == 1440:
                page.locator('#card-dental').screenshot(path=str(Path(__file__).parent / 'preview-dental.png'))
                page.locator('#card-beauty').screenshot(path=str(Path(__file__).parent / 'preview-beauty.png'))
                page.locator('#card-industrial').screenshot(path=str(Path(__file__).parent / 'preview-industrial.png'))
            first = page.locator('#card-dental button')
            first.evaluate("el => el.setAttribute('aria-expanded', 'true')")
            page.wait_for_timeout(300)
            assert page.locator('#card-dental .elementor-widget-container').evaluate("el => getComputedStyle(el, '::after').opacity") == '0'
            assert page.locator('#card-dental-2 .elementor-widget-container').evaluate("el => getComputedStyle(el, '::after').opacity") == '1'
            page.close()
            print(f'PASS: {label}: content, scoped presets, no horizontal overflow, fade state')
    finally:
        browser.close()
