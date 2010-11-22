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

require_once(dirname(__FILE__) . '/browser.php');

class MediaBrowseClasses extends MediaBrowser
{
	protected $modelClass = 'Media';
	protected $templateName = 'classes.phtml';
	protected $supportedTypes = array('text/html', 'application/json', 'application/rdf+xml');
	protected $kind = 'thing';
	protected $title = 'Things';
	protected $kindTitle;
	protected $children;
	protected $tvaNamespace;
	protected $scheme;

	protected function getObject()
	{
		$this->scheme = $this->object;
		$this->crumbName = $this->object->title;
		$this->addCrumb($this->request);
		$this->kindTitle = $this->title = $this->object->title;
		$this->kind = $this->object->singular;
		if(isset($this->object->tvaNamespace))
		{
			$this->tvaNamespace = $this->object->tvaNamespace;
			$this->supportedTypes[] = 'text/xml';
		}
		$parent = $this->object->uuid;
		while(null !== ($tag = $this->request->consume()))
		{
			if(!($obj = $this->model->locateObject($tag, $parent, $this->kind)))
			{				
				return $this->error(Error::OBJECT_NOT_FOUND);
			}
			$parent = $obj->uuid;
			$this->object = $obj;
			$this->crumbName = $this->object->title;
			$this->addCrumb($this->request);
		}
		$this->children = $this->model->query(array('parent' => $parent, 'kind' => $this->kind));
		if($this->object)
		{
			$this->title = $this->object->title;
			$this->objects = $this->model->query(array('parent' => null, 'kind' => array('episode', 'show', 'clip'), 'tags' => $this->object->uuid));
		}
		else
		{
			return $this->noObject();
		}
		return true;
	}
	
	protected function noObject()
	{
		return true;
	}
		
	protected function assignTemplate()
	{
		parent::assignTemplate();
		$this->vars['root'] = $this->request->base . $this->base;
		$this->vars['kindTitle'] = $this->kindTitle;
		$this->vars['children'] = $this->children;
		$uri = $this->request->pageUri;
		if(strlen($uri) > 1 && substr($uri, -1) == '/') $uri = substr($uri, 0, -1);
		$this->links[] = array('rel' => 'alternate', 'href' => $uri . '.rdf', 'type' => 'application/rdf+xml');
	}

	protected function perform_GET_XML()
	{
		$this->request->header('Content-type', 'text/xml');
		$this->request->flush();
		writeLn('<?xml version="1.0" encoding="UTF-8" ?>');
		writeLn('<ClassificationScheme uri="' . _e($this->tvaNamespace) . '" ' . 
				'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ' .
				'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" ' .
                'xmlns:owl="http://www.w3.org/2002/07/owl#">');
		foreach($this->children as $child)
		{
			$this->writeTVATerm($child);
		}
		writeLn('</ClassificationScheme>');
	}

	protected function writeTVATerm($node, $depth = "\t")
	{
		if(!isset($node->sameAs)) return;
		$termId = null;
		foreach($node->sameAs as $uri)
		{
			if(!strncmp($uri, $this->tvaNamespace, strlen($this->tvaNamespace)))
			{
				$termId = substr($uri, strlen($this->tvaNamespace));
				break;
			}
		}
		if($termId === null) return;
		writeLn($depth . '<Term termID="' . _e($termId) . '">');
		$ndepth = "\t" . $depth;
		writeLn($ndepth . '<Name>' . _e($node->title) . '</Name>');
		writeLn($ndepth . '<Definition>' . _e($node->title) . '</Definition>');
		foreach($node->sameAs as $same)
		{
			if(!strcmp($same, $uri)) continue;
			writeLn($ndepth . '<owl:sameAs rdf:resource="' . _e($same) . '" />');
		}
		$children = $this->model->query(array('kind' => $this->kind, 'parent' => $node->uuid));
		while(($child = $children->next()))
		{
			$this->writeTVATerm($child, $ndepth);
		}
		writeLn($depth . '</Term>');
	}
}
