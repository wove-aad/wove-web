<?php
/**
 * The site menu in the pinned bar: Back to top and the site links.
 */
?>
<div class="filter-bar__menu" id="filter-bar-menu" hidden>
  <ul role="list">
    <li><button type="button" data-top>Back to top <span aria-hidden="true">&uarr;</span></button></li>
    <li><a href="<?= url('our-work') ?>">Our work <span aria-hidden="true">&rarr;</span></a></li>
    <li><a href="<?= url('our-people') ?>">Our people <span aria-hidden="true">&rarr;</span></a></li>
    <li><a href="<?= url('contact') ?>">Get in touch <span aria-hidden="true">&rarr;</span></a></li>
  </ul>
</div>
