<?php

/**
 * admin process
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_process()
{
	$aliasFilter = new Redaxscript\Filter\Alias();
	$emailFilter = new Redaxscript\Filter\Email();
	$urlFilter = new Redaxscript\Filter\Url();
	$htmlFilter = new Redaxscript\Filter\Html();
	$aliasValidator = new Redaxscript\Validator\Alias();
	$loginValidator = new Redaxscript\Validator\Login();
	$specialFilter = new Redaxscript\Filter\Special;
	$messenger = new Redaxscript\Messenger();
	$filter = Redaxscript\Registry::get('filter');

	/* clean post */

	switch (TABLE_PARAMETER)
	{
		/* categories */

		case 'categories':
			$parent = $r['parent'] = $specialFilter->sanitize($_POST['parent']);

		/* articles */

		case 'articles':
			$r['keywords'] = $_POST['keywords'];
			$r['template'] = $specialFilter->sanitize($_POST['template']);

		/* extras */

		case 'extras':
			$title = $r['title'] = $_POST['title'];
			if (TABLE_PARAMETER != 'categories')
			{
				$r['headline'] = $specialFilter->sanitize($_POST['headline']);
			}
			$r['sibling'] = $specialFilter->sanitize($_POST['sibling']);
			$author = $r['author'] = MY_USER;

		/* comments */

		case 'comments':
			if (TABLE_PARAMETER == 'comments')
			{
				$r['url'] = $urlFilter->sanitize($_POST['url']);
				$author = $r['author'] = $_POST['author'];
			}
			if (TABLE_PARAMETER != 'categories')
			{
				$text = $r['text'] = $filter ? $htmlFilter->sanitize($_POST['text']) : $_POST['text'];
				$date = $r['date'] = $_POST['date'];
			}
			$rank = $r['rank'] = $specialFilter->sanitize($_POST['rank']);

		/* groups */

		case 'groups';
			if (TABLE_PARAMETER != 'comments')
			{
				$alias = $r['alias'] = $aliasFilter->sanitize($_POST['alias']);
			}

		/* users */

		case 'users':
			if (TABLE_PARAMETER != 'groups')
			{
				$language = $r['language'] = $specialFilter->sanitize($_POST['language']);
			}

		/* modules */

		case 'modules';
			$alias = $aliasFilter->sanitize($_POST['alias']);
			$status = $r['status'] = $specialFilter->sanitize($_POST['status']);
			if (TABLE_PARAMETER != 'groups' && TABLE_PARAMETER != 'users' && GROUPS_EDIT == 1)
			{
				$access = array_map(array($specialFilter, 'sanitize'), $_POST['access']);
				$access_string = implode(', ', $access);
				if ($access_string == '')
				{
					$access_string = null;
				}
				$access = $r['access'] = $access_string;
			}
			if (TABLE_PARAMETER != 'extras' && TABLE_PARAMETER != 'comments')
			{
				$r['description'] = $_POST['description'];
			}
			$token = $_POST['token'];
			break;
	}

	/* clean contents post */

	if (TABLE_PARAMETER == 'articles')
	{
		$r['infoline'] = $specialFilter->sanitize($_POST['infoline']);
		$comments = $r['comments'] = $specialFilter->sanitize($_POST['comments']);
		if ($category && ID_PARAMETER == '')
		{
			$status = $r['status'] = Redaxscript\Db::forTablePrefix('categories')->where('id', $category)->findOne()->status;
		}
	}
	if (TABLE_PARAMETER == 'articles' || TABLE_PARAMETER == 'extras')
	{
		$category = $r['category'] = $specialFilter->sanitize($_POST['category']);
	}
	if (TABLE_PARAMETER == 'articles' || TABLE_PARAMETER == 'extras' || TABLE_PARAMETER == 'comments')
	{
		if ($date > NOW)
		{
			$status = $r['status'] = 2;
		}
		else
		{
			$date = $r['date'] = NOW;
		}
	}
	if (TABLE_PARAMETER == 'extras' || TABLE_PARAMETER == 'comments')
	{
		$article = $r['article'] = $specialFilter->sanitize($_POST['article']);
	}
	if (TABLE_PARAMETER == 'comments' && ID_PARAMETER == '')
	{
		$status = $r['status'] = Redaxscript\Db::forTablePrefix('articles')->where('id', $article)->findOne()->status;
	}
	if (TABLE_PARAMETER == 'comments' || TABLE_PARAMETER == 'users')
	{
		$email = $r['email'] = $emailFilter->sanitize($_POST['email']);
	}

	/* clean groups post */

	if (TABLE_PARAMETER == 'groups' && (ID_PARAMETER == '' || ID_PARAMETER > 1))
	{
		$groups_array = array(
			'categories',
			'articles',
			'extras',
			'comments',
			'groups',
			'users',
			'modules'
		);
		foreach ($groups_array as $value)
		{
			$$value = array_map(array($specialFilter, 'sanitize'), $_POST[$value]);
			$groups_string = implode(', ', $$value);
			if ($groups_string == '')
			{
				$groups_string = 0;
			}
			$r[$value] = $groups_string;
		}
		$r['settings'] = $specialFilter->sanitize($_POST['settings']);
		$r['filter'] =  $specialFilter->sanitize($_POST['filter']);
	}
	if ((TABLE_PARAMETER == 'groups' || TABLE_PARAMETER == 'users') && ID_PARAMETER == 1)
	{
		$status = $r['status'] = 1;
	}
	if (TABLE_PARAMETER == 'groups' || TABLE_PARAMETER == 'users' || TABLE_PARAMETER == 'modules')
	{
		$name = $r['name'] = $specialFilter->sanitize($_POST['name']);
	}

	/* clean users post */

	if (TABLE_PARAMETER == 'users')
	{
		if ($_POST['user'])
		{
			$user = $r['user'] = $specialFilter->sanitize($_POST['user']);
		}
		else
		{
			$user = $r['user'] = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('id', ID_PARAMETER)->findOne()->user;
		}
		$password_check = $password_confirm = 1;
		if ($_POST['edit'] && $_POST['password'] == '' && $_POST['password_confirm'] == '' || $_POST['delete'])
		{
			$password_check = 0;
		}
		if ($_POST['password'] != $_POST['password_confirm'])
		{
			$password_confirm = 0;
		}
		$password = $specialFilter->sanitize($_POST['password']);
		if ($password_check == 1 && $password_confirm == 1)
		{
			$passwordHash = new Redaxscript\Hash(Redaxscript\Config::getInstance());
			$passwordHash->init($password);
			$r['password'] = $passwordHash->getHash();
		}
		if ($_POST['new'])
		{
			$r['first'] = $r['last'] = NOW;
		}
		if (ID_PARAMETER == '' || ID_PARAMETER > 1)
		{
			$groups = array_map(array($specialFilter, 'sanitize'), $_POST['groups']);
			$groups = array_map('clean', $groups);
			$groups_string = implode(', ', $groups);
			if ($groups_string == '')
			{
				$groups_string = 0;
			}
			$groups = $r['groups'] = $groups_string;
		}
	}
	$r_keys = array_keys($r);
	$last = end($r_keys);

	/* validate post */

	switch (TABLE_PARAMETER)
	{
		/* contents */

		case 'categories':
		case 'articles':
		case 'extras':
			if ($title == '')
			{
				$error = Redaxscript\Language::get('title_empty');
			}
			if (TABLE_PARAMETER == 'categories')
			{
				$opponent_id = Redaxscript\Db::forTablePrefix('articles')->where('alias', $alias)->findOne()->id;
			}
			if (TABLE_PARAMETER == 'articles')
			{
				$opponent_id = Redaxscript\Db::forTablePrefix('categories')->where('alias', $alias)->findOne()->id;
			}
			if ($opponent_id)
			{
				$error = Redaxscript\Language::get('alias_exists');
			}
			if (TABLE_PARAMETER != 'groups' && $aliasValidator->validate($alias, Redaxscript\Validator\Alias::MODE_GENERAL) == Redaxscript\Validator\ValidatorInterface::PASSED || $aliasValidator->validate($alias, Redaxscript\Validator\Alias::MODE_DEFAULT) == Redaxscript\Validator\ValidatorInterface::PASSED)
			{
				$error = Redaxscript\Language::get('alias_incorrect');
			}

		/* groups */

		case 'groups':
			if ($alias == '')
			{
				$error = Redaxscript\Language::get('alias_empty');
			}
			else
			{
				$alias_id = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('id', ID_PARAMETER)->findOne()->alias;
				$id_alias = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('alias', $alias)->findOne()->id;
			}
			if ($id_alias && strcasecmp($alias_id, $alias) < 0)
			{
				$error = Redaxscript\Language::get('alias_exists');
			}
	}

	/* validate general post */

	switch (TABLE_PARAMETER)
	{
		case 'articles':
		case 'extras':
		case 'comments':
			if ($text == '')
			{
				$error = Redaxscript\Language::get('text_empty');
			}
			break;
		case 'groups':
		case 'users':
		case 'modules':
			if ($name == '')
			{
				$error = Redaxscript\Language::get('name_empty');
			}
			break;
	}

	/* validate users post */

	if (TABLE_PARAMETER == 'users')
	{
		if ($user == '')
		{
			$error = Redaxscript\Language::get('user_incorrect');
		}
		else
		{
			$user_id = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('id', ID_PARAMETER)->findOne()->user;
			$id_user = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('user', $user)->findOne()->id;
		}
		if ($id_user && strcasecmp($user_id, $user) < 0)
		{
			$error = Redaxscript\Language::get('user_exists');
		}
		if ($loginValidator->validate($user) == Redaxscript\Validator\ValidatorInterface::FAILED)
		{
			$error = Redaxscript\Language::get('user_incorrect');
		}
		if ($password_check == 1)
		{
			if ($password == '')
			{
				$error = Redaxscript\Language::get('password_empty');
			}
			if ($password_confirm == 0 || $loginValidator->validate($password) == Redaxscript\Validator\ValidatorInterface::FAILED)
			{
				$error = Redaxscript\Language::get('password_incorrect');
			}
		}
	}

	/* validate last post */

	$emailValidator = new Redaxscript\Validator\Email();
	switch (TABLE_PARAMETER)
	{
		case 'comments':
			if ($author == '')
			{
				$error = Redaxscript\Language::get('author_empty');
			}
		case 'users':
			if ($emailValidator->validate($email) == Redaxscript\Validator\ValidatorInterface::FAILED)
			{
				$error = Redaxscript\Language::get('email_incorrect');
			}
	}
	$route = 'admin';

	/* handle error */

	if ($error)
	{
		if (ID_PARAMETER == '')
		{
			$route .= '/new/' . TABLE_PARAMETER;
		}
		else
		{
			$route .= '/edit/' . TABLE_PARAMETER . '/' . ID_PARAMETER;
		}

		/* show error */

		echo $messenger->setAction(Redaxscript\Language::get('back'), $route)->error($error, Redaxscript\Language::get('error_occurred'));
		return;
	}

	/* handle success */

	else
	{
		if (TABLE_EDIT == 1 || TABLE_DELETE == 1)
		{
			$route .= '/view/' . TABLE_PARAMETER;
			if ($alias)
			{
				$route .= '#' . $alias;
			}
			else if ($user)
			{
				$route .= '#' . $user;
			}
		}
	}

	/* empty and select to null */

	foreach ($r as $key => $value)
	{
		if ($value == '' || $value == 'select')
		{
			$r[$key] = null;
		}
	}

	/* process */

	switch (true)
	{
		/* query new */

		case $_POST['new']:
			Redaxscript\Db::forTablePrefix(Redaxscript\Registry::get('tableParameter'))
				->create()
				->set($r)
				->save();

			/* show success */

			echo $messenger->setAction(Redaxscript\Language::get('continue'), $route)->doRedirect()->success(Redaxscript\Language::get('operation_completed'));

			return;

		/* query edit */

		case $_POST['edit']:
			Redaxscript\Db::forTablePrefix(Redaxscript\Registry::get('tableParameter'))
				->whereIdIs(Redaxscript\Registry::get('idParameter'))
				->findOne()
				->set($r)
				->save();

			/* query categories */

			if (TABLE_PARAMETER == 'categories')
			{
				$categoryChildren = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('parent', ID_PARAMETER);
				$categoryArray = array_merge($categoryChildren->findFlatArray(), array(
					ID_PARAMETER
				));
				$articleChildren = Redaxscript\Db::forTablePrefix('articles')->whereIn('category', $categoryArray);
				$articleArray = $articleChildren->findFlatArray();
				if (count($articleArray) > 0)
				{
					Redaxscript\Db::forTablePrefix('comments')
						->whereIn('article', $articleArray)
						->findMany()
						->set(array(
							'status' => $status,
							'access' => $access
						))
						->save();
				}
				$categoryChildren
					->findMany()
					->set(array(
						'status' => $status,
						'access' => $access
					))
					->save();
				$articleChildren
					->findMany()
					->set(array(
						'status' => $status,
						'access' => $access
					))
					->save();
			}

			/* query articles */

			if (TABLE_PARAMETER == 'articles')
			{
				if ($comments == 0)
				{
					$status = 0;
				}
				Redaxscript\Db::forTablePrefix('comments')
					->where('article', ID_PARAMETER)
					->findMany()
					->set(array(
						'status' => $status,
						'access' => $access
					))
					->save();
			}
			if (USERS_EXCEPTION == 1)
			{
				$_SESSION[ROOT . '/my_name'] = $name;
				$_SESSION[ROOT . '/my_email'] = $email;
				if (file_exists('languages/' . $language . '.php'))
				{
					$_SESSION[ROOT . '/language'] = $language;
					$_SESSION[ROOT . '/language_selected'] = 1;
				}
			}

			/* show success */

			echo $messenger->setAction(Redaxscript\Language::get('continue'), $route)->doRedirect()->success(Redaxscript\Language::get('operation_completed'));
			return;
	}
}

/**
 * admin move
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_move()
{
	/* retrieve rank */

	$rank_asc = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->min('rank');
	$rank_desc = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->max('rank');
	$rank_old = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('id', ID_PARAMETER)->findOne()->rank;

	/* calculate new rank */

	$rank_new = 1;
	if (ADMIN_PARAMETER == 'up' && $rank_old > $rank_asc)
	{
		$rank_new = $rank_old - 1;
	}
	if (ADMIN_PARAMETER == 'down' && $rank_old < $rank_desc)
	{
		$rank_new = $rank_old + 1;
	}
	$id = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('rank', $rank_new)->findOne()->id;

	/* query rank */

	Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('id', $id)->findOne()->set('rank', $rank_old)->save();
	Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('id', ID_PARAMETER)->findOne()->set('rank', $rank_new)->save();

	/* show success */

	$messenger = new Redaxscript\Messenger();
	echo $messenger->setAction(Redaxscript\Language::get('continue'), 'admin/view/' . TABLE_PARAMETER)->doRedirect()->success(Redaxscript\Language::get('operation_completed'));
}

/**
 * admin sort
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_sort()
{
	if (TABLE_PARAMETER == 'categories' || TABLE_PARAMETER == 'articles' || TABLE_PARAMETER == 'extras' || TABLE_PARAMETER == 'comments')
	{
		/* query general select */

		$result = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->orderByAsc('rank')->findArray();

		/* build select array */

		if ($result)
		{
			foreach ($result as $r)
			{
				if ($r)
				{
					foreach ($r as $key => $value)
					{
						$$key = stripslashes($value);
					}
				}
				if (TABLE_PARAMETER == 'articles')
				{
					$parent = $category;
				}
				if (TABLE_PARAMETER == 'comments')
				{
					$parent = $article;
				}
				if ($parent)
				{
					$select_array[$parent][$id] = '';
				}
				else
				{
					$select_array[][$id] = '';
				}
			}
		}

		/* build update array */

		foreach ($select_array as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $key_sub => $value_sub)
				{
					$update_array[] = $key_sub;
				}
			}
			else
			{
				$update_array[] = $key;
			}
		}

		/* query general update */

		foreach ($update_array as $key => $value)
		{
			Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)
				->where('id', $value)
				->findOne()
				->set('rank', ++$key)
				->save();
		}
	}

	/* show success */

	$messenger = new Redaxscript\Messenger();
	echo $messenger->setAction(Redaxscript\Language::get('continue'), 'admin/view/' . TABLE_PARAMETER)->doRedirect()->success(Redaxscript\Language::get('operation_completed'));
}

/**
 * admin status
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 *
 * @param string $input
 */

function admin_status($input = '')
{
	Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)
		->where('id', ID_PARAMETER)
		->findMany()
		->set('status', $input)
		->save();

	/* query categories */

	if (TABLE_PARAMETER == 'categories')
	{
		$categoryChildren = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('parent', ID_PARAMETER);
		$categoryArray = array_merge($categoryChildren->findFlatArray(), array(
			ID_PARAMETER
		));
		$articleChildren = Redaxscript\Db::forTablePrefix('articles')->whereIn('category', $categoryArray);
		$articleArray = $articleChildren->findFlatArray();
		if (count($articleArray) > 0)
		{
			Redaxscript\Db::forTablePrefix('comments')
				->whereIn('article', $articleArray)
				->findMany()
				->set('status', $input)
				->save();
		}
		$categoryChildren->findMany()->set('status', $input)->save();
		$articleChildren->findMany()->set('status', $input)->save();
	}

	/* query articles */

	if (TABLE_PARAMETER == 'articles')
	{
		Redaxscript\Db::forTablePrefix('comments')
			->where('article', ID_PARAMETER)
			->findMany()
			->set('status', $input)
			->save();
	}

	/* show success */

	$messenger = new Redaxscript\Messenger();
	echo $messenger->setAction(Redaxscript\Language::get('continue'), 'admin/view/' . TABLE_PARAMETER)->doRedirect()->success(Redaxscript\Language::get('operation_completed'));
}

/**
 * admin install
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_install()
{
	if (TABLE_PARAMETER == 'modules')
	{
		/* install module */

		if (is_dir('modules/' . ALIAS_PARAMETER))
		{
			$module = Redaxscript\Db::forTablePrefix('modules')->where('alias', ALIAS_PARAMETER)->findOne()->id;
			if ((ADMIN_PARAMETER == 'install' && $module == '') || (ADMIN_PARAMETER == 'uninstall' && $module))
			{
				$object = 'Redaxscript\Modules\\' . ALIAS_PARAMETER . '\\' . ALIAS_PARAMETER;

				/* method exists */

				if (method_exists($object, ADMIN_PARAMETER))
				{
					call_user_func(array($object, ADMIN_PARAMETER));
				}
			}
		}
	}

	/* show success */

	$messenger = new Redaxscript\Messenger();
	echo $messenger->setAction(Redaxscript\Language::get('continue'), 'admin/view/' . TABLE_PARAMETER . '#' . ALIAS_PARAMETER)->doRedirect()->success(Redaxscript\Language::get('operation_completed'));
}

/**
 * admin delete
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_delete()
{
	if (TABLE_PARAMETER == 'categories' || TABLE_PARAMETER == 'articles' || TABLE_PARAMETER == 'extras' || TABLE_PARAMETER == 'comments' || TABLE_PARAMETER == 'groups' || TABLE_PARAMETER == 'users')
	{
		Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)
			->where('id', ID_PARAMETER)
			->findMany()
			->delete();
	}

	/* query categories */

	if (TABLE_PARAMETER == 'categories')
	{
		$categoryChildren = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('parent', ID_PARAMETER);
		$categoryArray = array_merge($categoryChildren->findFlatArray(), array(
			ID_PARAMETER
		));
		$articleChildren = Redaxscript\Db::forTablePrefix('articles')->whereIn('category', $categoryArray);
		$articleArray = $articleChildren->findFlatArray();
		if (count($articleArray) > 0)
		{
			Redaxscript\Db::forTablePrefix('comments')
				->whereIn('article', $articleArray)
				->findMany()
				->delete();
		}
		$categoryChildren->findMany()->delete();
		$articleChildren->findMany()->delete();

		/* reset extras */

		Redaxscript\Db::forTablePrefix('extras')
			->whereIn('category', $categoryArray)
			->findMany()
			->set('category', 0)
			->save();
	}

	/* query articles */

	if (TABLE_PARAMETER == 'articles')
	{
		Redaxscript\Db::forTablePrefix('comments')
			->where('article', ID_PARAMETER)
			->findMany()
			->delete();

		/* reset extras */

		Redaxscript\Db::forTablePrefix('extras')
			->where('article', ID_PARAMETER)
			->findMany()
			->set('article', 0)
			->save();

		/* reset homepage */

		if (ID_PARAMETER == Redaxscript\Db::getSetting('homepage'))
		{
			Redaxscript\Db::forTablePrefix('settings')
				->where('name', 'homepage')
				->findOne()
				->set('value', 0)
				->save();
		}
	}

	/* handle exception */

	if (USERS_EXCEPTION == 1)
	{
		logout();
	}

	/* handle success */

	else
	{
		$route = 'admin';
		if (TABLE_EDIT == 1 || TABLE_DELETE == 1)
		{
			$route .= '/view/' . TABLE_PARAMETER;
		}

		/* show success */

		$messenger = new Redaxscript\Messenger();
		echo $messenger->setAction(Redaxscript\Language::get('continue'), $route)->doRedirect()->success(Redaxscript\Language::get('operation_completed'));
	}
}

/**
 * admin update
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_update()
{
	if (TABLE_PARAMETER == 'settings')
	{
		$specialFilter = new Redaxscript\Filter\Special();
		$emailFilter = new Redaxscript\Filter\Email();

		/* clean post */

		$r['language'] = $specialFilter->sanitize($_POST['language']);
		$r['template'] = $specialFilter->sanitize($_POST['template']);
		$r['title'] = $_POST['title'];
		$r['author'] = $_POST['author'];
		$r['copyright'] = $_POST['copyright'];
		$r['description'] = $_POST['description'];
		$r['keywords'] = $_POST['keywords'];
		$r['robots'] = $specialFilter->sanitize($_POST['robots']);
		$r['email'] = $emailFilter->sanitize($_POST['email']);
		$r['subject'] = $_POST['subject'];
		$r['notification'] = $specialFilter->sanitize($_POST['notification']);
		$r['charset'] = !$r['charset'] ? 'utf-8' : $r['charset'];
		$r['divider'] = $_POST['divider'];
		$r['time'] = $_POST['time'];
		$r['date'] =  $_POST['date'];
		$r['homepage'] = $specialFilter->sanitize($_POST['homepage'], 0);
		$r['limit'] = !$specialFilter->sanitize($_POST['limit']) ? 10 : $specialFilter->sanitize($_POST['limit']);
		$r['order'] = $specialFilter->sanitize($_POST['order']);
		$r['pagination'] = $specialFilter->sanitize($_POST['pagination']);
		$r['moderation'] = $specialFilter->sanitize($_POST['moderation']);
		$r['registration'] = $specialFilter->sanitize($_POST['registration']);
		$r['verification'] = $specialFilter->sanitize($_POST['verification']);
		$r['recovery'] = $specialFilter->sanitize($_POST['recovery']);
		$r['captcha'] = $specialFilter->sanitize($_POST['captcha']);

		/* update settings */

		foreach ($r as $key => $value)
		{
			Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)
				->where('name', $key)
				->findOne()
				->set('value', $value)
				->save();
		}

		/* show success */

		$messenger = new Redaxscript\Messenger();
		echo $messenger->setAction(Redaxscript\Language::get('continue'), 'admin/edit/settings')->doRedirect()->success(Redaxscript\Language::get('operation_completed'));
	}
}

/**
 * admin last update
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_last_update()
{
	if (MY_ID)
	{
		Redaxscript\Db::forTablePrefix('users')
			->where('id', MY_ID)
			->findOne()
			->set('last', NOW)
			->save();
	}
}
