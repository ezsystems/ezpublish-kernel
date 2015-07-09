# Query controller

> Status: must have

## Example and usage

```twig
{{ render(
	controller(
		ez_named_query,
		{
			'identifier': 'AcmeBundle:LatestArticles',
			'parameters': {'category': 'marketing'}
		}
	)
) }}
```

## Rendering
