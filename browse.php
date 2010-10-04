<?php

/*
 * programmes: A front-end to an asset store
 *
 * Copyright 2010 Mo McRoberts.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

uses('uuid');

require_once(MODULES_ROOT . 'media/model.php');

class MediaBrowse extends Page
{
	protected $modelClass = 'Media';
	protected $templateName = 'browse.phtml';
	protected $title = 'Programmes';
	protected $crumbName = 'Programmes';

	public function __construct()
	{
		parent::__construct();
		$this->routes['a-z'] = array('file' => 'browse-a-z.php', 'class' => 'MediaBrowseAZ');
	}
	
	protected function getObject()
	{
		if(null === ($tag = $this->request->consume()))
		{
			return true;
		}
		if(($uuid = UUID::isUUID($tag)))
		{
			$this->object = $this->model->objectForUUID($uuid);
		}
		else
		{
			$this->object = $this->model->locateObject($tag);
		}
		if($this->object)
		{
			if($this->object instanceof Episode)
			{
				if(isset($this->object->series) || isset($this->object->show))
				{
					$this->request->redirect($this->request->base . $this->object->relativeURI);
					return false;
				}
				require_once(dirname(__FILE__) . '/browse-episode.php');
				$inst = new MediaBrowseEpisode();
				$inst->object = $this->object;
				$inst->process($this->request);
				return false;
			}
			if($this->object instanceof Show)
			{
				if($this->object->kind == 'series')
				{
					$this->request->redirect($this->request->base . $this->object->relativeURI);
				}
				require_once(dirname(__FILE__) . '/browse-show.php');
				$inst = new MediaBrowseShow();
				$inst->object = $this->object;
				$inst->process($this->request);
				return false;
			}
			if($this->object instanceof Scheme)
			{
				require_once(dirname(__FILE__) . '/browse-classes.php');
				$inst = new MediaBrowseClasses();
				$inst->object = $this->object;
				$inst->process($this->request);
				return false;
			}
			print_r($this->object);
			die();
		}
		return $this->error(Error::OBJECT_NOT_FOUND);
	}
}
