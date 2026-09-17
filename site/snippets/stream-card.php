<?php
/**
 * Stream card — renders the right card type based on content template
 * Usage: <?php snippet('stream-card', ['post' => $entry]) ?>
 */
if ($post->intendedTemplate()->name() === 'case-study') {
  snippet('work-grid-card', ['caseStudy' => $post]);
} else {
  snippet('feed-card', ['post' => $post]);
}
