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

require_once(dirname(__FILE__) . '/browser.php');

class MediaBrowseShow extends MediaBrowser
{
	protected $templateName = 'show.phtml';
	protected $supportedTypes = array('text/html', 'application/json', 'application/rdf+xml', 'application/atom+xml');
	protected $series;
	protected $episodes;

	protected function getObject()
	{
		$this->title = $this->object->title;
		$this->crumbName = $this->object->title;
		$this->addCrumb();
		if(null !== ($tag = $this->request->consume()))
		{
			$obj = $this->model->locateObject($tag, $this->object->uuid);
			if(!$obj)
			{				
				return $this->error(Error::OBJECT_NOT_FOUND);
			}
			switch($obj->kind)
			{
			case 'episode':
				if(isset($obj->series))
				{
					$this->request->redirect($this->request->base . $obj->relativeURI);
					return false;
				}
				require_once(dirname(__FILE__) . '/browse-episode.php');
				$inst = new MediaBrowseEpisode();
				$inst->object = $obj;
				$inst->process($this->request);
				return false;
			case 'series':
				require_once(dirname(__FILE__) . '/browse-series.php');
				$inst = new MediaBrowseSeries();
				$inst->object = $obj;
				$inst->process($this->request);
				return false;
			}
			print_r($obj);
			die('-- unhandled object --');
		}
		$this->series = $this->model->query(array('kind' => 'series', 'parent' => $this->object->uuid));
		if($this->series->EOF)
		{
			$this->series = null;
		}
		$this->episodes = $this->model->query(array('kind' => 'episode', 'parent' => $this->object->uuid));
		if($this->episodes->EOF)
		{
			$this->episodes = null;
		}
		return true;
	}
	
	protected function assignTemplate()
	{
		parent::assignTemplate();
		if(!isset($this->vars['page_type'])) $this->vars['page_type'] = '';
		$this->vars['series'] = $this->series;
		$this->vars['episodes'] = $this->episodes;
		$this->vars['clips'] = null;
		if(isset($this->object['template']))
		{
			$this->vars['custom'] = $this->object['template'];
			if(isset($this->object['template']['pageType']))
			{
				$this->vars['page_type'] .= ' ' . $this->object['template']['pageType'];
			}
		}
	}
}