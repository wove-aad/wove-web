<?php

// ====================================================================
// WOVE MIND ENTRIES COLLECTION
// ====================================================================
// $woveMindEntries = $page->children()->listed()->sortBy('date', 'desc')->paginate(9);
return function () {
    
    return page('wove-mind')
        ->children()
        ->listed()
        ->sortBy('date', 'desc');

};
