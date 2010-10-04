<?php

/*
 * media: The media metadata model
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

require_once(dirname(__FILE__) . '/browse-show.php');

class MediaBrowseSeries extends MediaBrowseShow
{
	protected $series;
	protected $episodes;

	protected function getObject()
	{
		$this->title = $this->object->title;
		$this->crumbName = $this->object->title;
		$this->addCrumb();
		if(null !== ($tag = $this->request->consume()))
		{
			$obj = null;
			if(null !== ($uuid = UUID::isUUID($tag)))
			{
				$rs = $this->model->query(array('uuid' => $uuid, 'parent' => $this->object->uuid));
				$obj = $rs->next();
			}
			else
			{
				$rs = $this->model->query(array('tag' => $tag, 'parent' => $this->object->uuid));
				$obj = $rs->next();
			}
			if(!$obj)
			{				
				return $this->error(Error::OBJECT_NOT_FOUND);
			}
			switch($obj->kind)
			{
			case 'episode':
				require_once(dirname(__FILE__) . '/browse-episode.php');
				$inst = new MediaBrowseEpisode();
				$inst->object = $obj;
				$inst->process($this->request);
				return false;
			}
			print_r($obj);
			die('-- unhandled object --');
		}
		$this->object->merge();
		$this->episodes = $this->model->query(array('kind' => 'episode', 'parent' => $this->object->uuid));
		if($this->episodes->EOF)
		{
			$this->episodes = null;
		}
		return true;
	}									   
}