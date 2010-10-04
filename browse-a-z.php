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

require_once(MODULES_ROOT . 'media/model.php');

class MediaBrowseAZ extends Page
{	
	protected $templateName = 'list.phtml';
	protected $modelClass = 'Media';
	protected $crumbName = 'Browse A-Z';

	protected function getObject()
	{
		if(strcmp($this->request->consume(), 'by'))
		{
			return $this->error(Error::OBJECT_NOT_FOUND);
		}
		$letter = strtolower($this->request->consume());
		if(!ctype_alpha($letter) && $letter != '*')
		{
			return $this->error(Error::OBJECT_NOT_FOUND);
		}
		if($letter == '*')
		{
			$this->crumbName = '0-9';
		}
		else
		{
			$this->crumbName = strtoupper($letter);
		}
		$this->objects = $this->model->query(array('parent' => null, 'kind' => array('show', 'episode'), 'title_firstchar' => $letter));		
		$this->addCrumb($this->request);
		return true;
	}
}
