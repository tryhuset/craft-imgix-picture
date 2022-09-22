# Craft Imgix Picture plugin for Craft CMS 4.x

Create responsive image tags and json objects from config file, using the imgIX service.


## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2.  Register repository:

        ...
        "repositories": [
          ...
          {
            "type": "vcs",
            "url": "https://git1.apt.no/open/craft-imgix-picture.git"
          }
        ],
        ...

3. Then tell Composer to load the plugin:

```sh
composer require apt/craft-imgix-picture
```

4. In the Control Panel, go to Settings → Plugins and click the “Install” button for Craft Imgix Picture.

## Register your image styles

Create a settings fil in your config directory.

`config/craft-imgix-picture.php`

with the following code

```php
<?php
return [
    // 'variableName' => 'craftImgixPicture', // override craft variable name

    // Imgix API key
    'apiKey'         => '',

    // Volume handles mapped to Imgix domains
    'domains'   => [],

    // Imgix signed URLs token
    'signedToken' => '',

    // Lazy load attribute prefix
    'lazyLoadPrefix' => '',

    // 'options' => [], // Default imgix options
    'imageStyles' => [
        'complex-picture' => [
            // 'options' => [], // Override default imgix options
            'sources' => [
                [
                    'media' => '(max-width: 600px)',
                    'aspectRatio' => 4 / 3,
                    'sizes' => '100px',
                    'widths' => [100, 200],
                    // 'options' => [], // Override imgix options
                ],
                [
                    'media' => '(max-width: 1200px)',
                    'sizes' => '300px',
                    'widths' => [300, 600],
                ]
            ],
            'img' => [
                'aspectRatio' => 260 / 280,
                'sizes' => '50vw',
                'widths' => [260, 260 * 2, 260 * 3],
                'alt' => 'foo',
                'class' => 'foo', // Add your html attributes
                // 'options' => [], // Override imgix options
            ],
        ],

        'simple-image' => [
          'img' => [
                'widths' => [260],
                'alt' => 'foo',
            ],
        ]            
    ],
];
```

## Render html tag
```twig
{{ craft.craftImgixPicture.tag(asset, 'complex-picture', { alt: asset.title, pictureClass: 'foo', class: 'bar' }) }}
```

outputs:

```html
<picture class="foo">
  <source srcset="..." sizes="100vw" media="(max-width: 600px)" />
  <source srcset="..." sizes="300px" media="(max-width: 1200px)" />
  <img class="bar" srcset="..." src="..." sizes="50vw" alt="qux" />
</picture>
```

```twig
{{ craft.craftImgixPicture.tag(asset, 'simple-image') }}
```

outputs:

```html
<img class="bar" src="..." alt="foo" />
```

## Image as array

```twig
{% set image = craft.craftImgixPicture.array(asset, 'complex-picture', { alt: asset.title, pictureClass: 'foo', class: 'bar' }) %}
```

outputs:

```javascript

{
  class: 'foo',
  sources: [
    {
      media: "(max-width: 600px)",
      sizes: "100vw",
      srcSet: '....',
    },
    {
      media: "(max-width: 1200px)",
      sizes: "300px",
      srcSet: '....',
    },
  ],
  img: {
    sizes: "50vw",
    srcSet: '....',
    src: '...',
    alt: 'foo',
    className: 'bar',
  }
}

```

```twig
{{ craft.craftImgixPicture.array(asset, 'simple-image') }}
```

outputs:

```javascript

{
  img: {
    src: '...',
    alt: 'foo',
  }
}

```

Brought to you by [thomas@apt.no](https://apt.no)

## GraphQL

Query

```javascript

{
  entry(section: "homepage") {
    id
    title
    ... on homepage_homepage_Entry {
      mainImage {
        id
        picture: imgixPicture(style: "thumb") {
          sources {
            media
            srcSet
            sizes
          }
          img {
            alt
            src
            srcSet
            sizes
          }
        }
      }
    }
  }
}

```

outputs:

```javascript

{
  "data": {
    "entry": {
      "id": "3",
      "title": "Homepage",
      "mainImage": [
        {
          "id": "17",
          "picture": {
            "sources": [
              {
                "media": "(max-width: 600px)",
                "srcSet": "...",
                "sizes": "100px"
              },
              {
                "media": "(max-width: 1200px)",
                "srcSet": "...",
                "sizes": "300px"
              }
            ],
            "img": {
              "alt": "IMG 0120",
              "src": "...",
              "srcSet": "...",
              "sizes": "50vw",
              "alt": "foo",
              "className": "bar"
            }
          }
        }
      ]
    }
  }
}

```
