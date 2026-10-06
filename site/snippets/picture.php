<?php
/**
 * Responsive image: a <picture> with AVIF and WebP sources and a fallback
 * <img> in the original format, all with srcset and sizes.
 *
 * Usage:
 *   <?php snippet('picture', [
 *     'file'   => $image,              // Kirby File (required)
 *     'widths' => [400, 800, 1200],    // candidate widths in px
 *     'sizes'  => '(min-width: 960px) 33vw, 100vw',
 *     'ratio'  => 16 / 10,             // optional: crop to width / height
 *     'alt'    => '',
 *     'class'  => '',                  // goes on the <img>
 *     'loading' => 'lazy',             // 'eager' for images in the first viewport
 *     'fetchpriority' => null,         // 'high' for the hero image
 *     'attrs'  => [],                  // any other <img> attributes
 *     'avifQuality' => 60,             // AVIF needs a lower number than JPEG/WebP
 *   ]) ?>
 *
 * - Widths larger than the original (or than the crop allows) are dropped,
 *   so images are never upscaled.
 * - SVGs, GIFs (to keep animation) and other files Kirby can't resize are
 *   output as a plain <img> of the original.
 * - The AVIF source is only output when the thumbs driver can encode AVIF
 *   (GD built with AVIF support, or Imagick with an AVIF codec). Kirby
 *   throws when asked for a format the driver can't write, and browsers
 *   don't fall back from a <source> that fails to load. Set the
 *   'picture.avif' option to true or false to override the check.
 * - `picture { display: contents }` in site.css keeps the wrapper out of
 *   layout, so existing `.parent img` and flex/grid rules still apply.
 */

$file = $file ?? null;
if (!$file) return;

$widths        = $widths ?? [320, 640, 960, 1280, 1920];
$sizes         = $sizes ?? '100vw';
$ratio         = $ratio ?? null;
$alt           = $alt ?? '';
$class         = $class ?? null;
$loading       = $loading ?? 'lazy';
$fetchpriority = $fetchpriority ?? null;
$quality       = $quality ?? null;
$attrs         = $attrs ?? [];
$avifQuality   = $avifQuality ?? 60;

$avif = option('picture.avif');
if ($avif === null) {
  $avif = match (option('thumbs.driver', 'gd')) {
    'gd'      => function_exists('imageavif') && (gd_info()['AVIF Support'] ?? false),
    'imagick' => class_exists('Imagick') && \Imagick::queryFormats('AVIF') !== [],
    default   => false,
  };
}

// Builds the <img> tag by hand: Html::tag() drops empty attributes, and
// decorative images need alt="".
$img = function (array $extra) use ($alt, $class, $loading, $fetchpriority, $attrs) {
  $all = array_merge([
    'class'         => $class,
    'alt'           => $alt,
    'loading'       => $loading,
    'decoding'      => $loading === 'eager' ? null : 'async',
    'fetchpriority' => $fetchpriority,
  ], $extra, $attrs);
  $out = '<img';
  foreach ($all as $name => $value) {
    if ($value === null || ($value === '' && $name !== 'alt')) continue;
    $out .= ' ' . $name . '="' . html((string) $value) . '"';
  }
  return $out . '>';
};

if ($file->isResizable() === false || $file->extension() === 'gif') {
  echo $img([
    'src'    => $file->url(),
    'width'  => $file->width() ?: null,
    'height' => $file->height() ?: null,
  ]);
  return;
}

// Largest width we can produce without upscaling, given the crop ratio.
$maxWidth = $file->width();
if ($ratio) {
  $maxWidth = min($maxWidth, (int) floor($file->height() * $ratio));
}

$widths = array_values(array_unique(array_filter($widths, fn ($w) => $w <= $maxWidth)));
sort($widths);
if ($widths === []) $widths = [$maxWidth];

$options = function (int $w, ?string $format = null) use ($ratio, $quality) {
  $o = ['width' => $w];
  if ($ratio) {
    $o['height'] = (int) round($w / $ratio);
    $o['crop']   = true;
  }
  if ($quality) $o['quality'] = $quality;
  if ($format)  $o['format']  = $format;
  return $o;
};

$set = function (?string $format = null) use ($file, $widths, $options, $avifQuality) {
  $list = [];
  foreach ($widths as $w) {
    $list[$w . 'w'] = $options($w, $format);
    if ($format === 'avif') $list[$w . 'w']['quality'] = $avifQuality;
  }
  return $file->srcset($list);
};

// Fallback src: the largest width up to 1200px.
$fallbackWidth = max(array_filter($widths, fn ($w) => $w <= 1200) ?: [$widths[0]]);
$fallback      = $file->thumb($options($fallbackWidth));
$height        = (int) round($ratio ? $fallbackWidth / $ratio : $fallbackWidth * $file->height() / $file->width());
?>
<picture>
  <?php if ($avif && $file->extension() !== 'avif'): ?>
  <source type="image/avif" srcset="<?= $set('avif') ?>" sizes="<?= html($sizes) ?>">
  <?php endif ?>
  <?php if ($file->extension() !== 'webp'): ?>
  <source type="image/webp" srcset="<?= $set('webp') ?>" sizes="<?= html($sizes) ?>">
  <?php endif ?>
  <?= $img([
    'src'    => $fallback->url(),
    'srcset' => $set(),
    'sizes'  => $sizes,
    'width'  => $fallbackWidth,
    'height' => $height,
  ]) ?>
</picture>
