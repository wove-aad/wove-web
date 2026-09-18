<?php

// ====================================================================
// CASE STUDIES COLLECTION
// ====================================================================
// $caseStudies = $page->children()->listed()->sortBy('date', 'desc')->paginate(9);
return function () {
    $work = page('work');
    if (!$work) return new \Kirby\Cms\Pages();

    return $work
        ->children()
        ->listed()
        ->sortBy('date', 'desc');

};
