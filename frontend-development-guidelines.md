# Frontend Development Guidelines 

We're building a mobile-first responsive HTML / CSS component library (no CSS framework) from some Figma prototypes. These components will be used to create HTML section blocks which in turn will be used to create page templates to be integrated in Kirby CMS, where core content will be managed. Some page templates will be relatively static HTML/PHP and other page templates will be made of comprised of dynamic CMS content from predefined Kirby blueprints and content blocks.

## Structure
We’ll work component by component (frame by frame in Figma) and then build section element blocks from the components, and then build the page templates from the section blocks, with global header element at the top, footer element at the bottom and a main element in the middle containing the page section blocks.

## HTML Markup
* Use lean semantic HTML markup and Aria to ensure that the pages, components and sections generated are compliant with WCAG 2.2 level AA accessibility standards. 
* Include keyboard navigable skip link to the main content element.

## CSS
* Use Design tokens (custom css variables) and the [Cube CSS methodology](https://cube.fyi/) to build the components and layouts.
* When creating custom blocks and component CSS classes to extend the foundational Cube CSS, use the BEM naming convention and CSS nesting to keep things modular.
* All components and layouts should be flexible and mobile-first responsive, i.e. using min-width media queries — both a mobile layout and desktop layout (figma frame) will be provided.

## Images 
* Page speed and performance are critical so use optimised SVG’s for illustrated graphic where appropriate. 
* Where there are raster images on the page, avoid using CSS background images and use responsive images (srcset and sizes) for the img element instead. Lazy load all images which are not visible within the initially loaded viewport window and assign high fetchpriority to hero images that load within the initial viewport.

## Files & Folder structure:
* Build up the library using the folder structure provided and assets provided. We’ll be using LightingCSS to process and minify the  CSS. 
* prefix all CSS partials with an underscore (_)


