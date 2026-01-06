# Craft Imgix Picture plugin for Craft CMS 5.x

Create responsive image tags and json objects from config file, using the imgIX service.

## Installation

To install the plugin, follow these instructions.

1.  Open your terminal and go to your Craft project:

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

3.  Then tell Composer to load the plugin:

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

    // Add file extentions that should skip transform. Typical prevent destorying animated gifs.
    //'exclude' => ['gif'],

    // 'options' => [], // Default imgix options
    'imageStyles' => [
        'complex-picture' => [
            // 'options' => [], // Override default imgix options
            // Add file extentions that should skip transform. Typical prevent destorying animated gifs.
            //'exclude' => ['gif'],
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
                // 'width' => true, // If you want calculated width
                // 'height' => true // If you want calculated height
            ],
        ],

        'simple-image' => [
          'img' => [
                'widths' => [260],
                'alt' => 'foo',
            ],
        ],
        'my-video' => [
          'fm' => 'mp4', // default 'mp4'
          'res' => 'high', // default 'height'
          'controls' => true,
          'autoplay' => true,
          'loop' => true,
          'muted' => true,
          'playsinline' => true,
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

## Image preload tags

Sometimes it is advantageous to render preload tags in the head of the page, especially when you have full-coverage images above the fold.

```twig
<head>
...
{{ craft.craftImgixPicture.preload(asset, 'complex-picture') }}
</head>
```

outputs:

```html
<link rel="preload" href="..." as="image" imagesrcset="..." imagesizes="50vw" />
<link
  rel="preload"
  as="image"
  imagesrcset="..."
  imagesizes="100vw"
  media="(max-width: 600px)"
/>
<link
  rel="preload"
  as="image"
  imagesrcset="..."
  imagesizes="300px"
  media="(max-width: 1200px)"
/>
```

## Render Video html tag

```twig
<head>
...
{{ craft.craftImgixPicture.tag(asset, 'my-video') }}
</head>
```

outputs:

```html
<video
  src="...mp4?fm=mp4&res=high"
  controls
  autoplay
  loop
  muted
  playsinline
></video>
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

## Video as array

```twig
{% set image = craft.craftImgixPicture.array(asset, 'my-video') %}
```

outputs:

```javascript

{
  video: {
    src: '...mp4?fm=mp4&res=high',
    autoplay: true,
    loop: true,
    muted: true,
    playsinline: true
  }
}

```

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

Brought to you by [thomas.somoen@try.no](mailto:thomas.somoen@try.no)
