<?php

// ====================================================================
// CASE STUDIES COLLECTION
// ====================================================================
// $caseStudies = $page->children()->listed()->sortBy('date', 'desc')->paginate(9);
return function () {
    
    return page('work')
        ->children()
        ->listed()
        ->sortBy('date', 'desc');

};
