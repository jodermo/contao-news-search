# Contao 4 - News Search Bundle
##### • Search for news by categories
###### extension for contao/core-bundle (version ^4.9)


By Moritz Petzka [petzka.com](https://petzka.com)

Contao website:[https://contao.org](https://contao.org) <br>
Official Contao Documentation: [https://docs.contao.org](https://docs.contao.org) <br><br>
How to work with Contao 4 and Troubleshooting: [jodermo.github.io/contao-4-documentation](https://jodermo.github.io/contao-4-documentation/)


<br>


## Add Bundle as Git-Repository<br>
add code to \<contao root path\>/composer.json
```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/jodermo/contao-news-search.git"
        }
    ],
    "require": {
        "petzka/contao-news-search": "dev-master"
    },
    "config": {
        "preferred-install": {
            "petzka/*": "source",
            "*": "dist"
        }
    },
}
```

## Add Bundle as local Repository<br>
add code to \<contao root path\>/composer.json
```json
{
    "...": "...",
    "repositories": [
        {
            "type": "path",
            "url": "repositories/contao-article-categories"
        }
    ],
    "require": {
        "...": "...",
        "petzka/article-categories": "dev-master"
    },
    "config": {
        "preferred-install": {
            "petzka/*": "source",
            "*": "dist"
        }
    },
}
```
