Datagrid
========

This is simple datagrid for Nette framework

Installation
------------

The best way to install brabijan/datagrid is using  [Composer](http://getcomposer.org/):

```sh
$ composer require brabijan/datagrid:@dev
```

Usage
-----

```php
protected function createComponentDatagrid() {
	$data = array(
		array(
			"id" => 1,
			"name" => "Jan",
			"surname" => "Brabec",
			"birth" => "2.1.1991",
			"age" => 22
		),
		array(
			"id" => 2,
			"name" => "Peter",
			"surname" => "Griffin",
			"birth" => "18.6.1969",
			"age" => 42
		),
	);

	$datagrid = new Brabijan\Datagrid\Renderer();
	// $datagrid->addCollumn($title, $parameter[, $format])
	$datagrid->addCollumn("edit", "id", '<a n:href="Presenter:edit $id"><i class="icon-edit"></i></a>')->hideTitle();
	$datagrid->addCollumn("remove", "id", '<a n:href="delete! $id"><i class="icon-trash"></i></a>')->hideTitle();
	$datagrid->addCollumn("Full name", array("name", "surname"));
	$datagrid->addCollumn("Date of birth (age)", array("birth", "age"), '{$birth} ({$age} y.o.)');
	$datagrid->setRowPrimaryKey("id");
	$datagrid->setData($data);

	return $datagrid->getRenderer();
}
```

Output
------

```html
<table>
	<tr>
		<th></th>
		<th></th>
		<th>Full name</th>
		<th>Date of birth (age)</th>
	</tr>
	<tr>
		<td><a href="/presenter/edit/1"><i class="icon-edit"></i></a></td>
		<td><a href="/presenter/edit/1?do=delete"><i class="icon-edit"></i></a></td>
		<td>Jan Brabec</td>
		<td>2.1.1991 (22 y.o.)</td>
	</tr>
	<tr>
		<td><a href="/presenter/edit/2"><i class="icon-edit"></i></a></td>
		<td><a href="/presenter/edit/2?do=delete"><i class="icon-edit"></i></a></td>
		<td>Peter Griffin</td>
		<td>18.6.1969 (43 y.o.)</td>
	</tr>
</table>
```
