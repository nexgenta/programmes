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

class MediaBrowseVersion extends MediaBrowser
{	
	protected $modelClass = 'Media';
	protected $supportedTypes = array('application/json', 'application/rdf+xml', 'application/atom+xml');
	public $episode = null;

	protected function getObject()
	{
		$this->object->merge();
		$this->title = $this->object->title;
		if(!strlen($this->title))
		{
			if(strlen($this->object->slug))
			{
				$this->title = $this->object->slug;
			}
			else
			{
				$this->title = $this->object->uuid;
			}
		}
		return true;
	}
	
	protected function perform_GET_RDF()
	{
		$this->request->header('Content-type', 'application/rdf+xml');
		$this->request->flush();
		$uri = $this->request->pageUri;
		if(strlen($uri) > 1 && substr($uri, -1) == '/') $uri = substr($uri, 0, -1);		
		writeLn('<?xml version="1.0" encoding="utf-8" ?>');
		writeLn('<rdf:RDF ' .
				'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ' .
				'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" ' .
				'xmlns:owl="http://www.w3.org/2002/07/owl#" ' .
				'xmlns:foaf="http://xmlns.com/foaf/0.1/" ' .
				'xmlns:po="http://purl.org/ontology/po/" ' .
				'xmlns:mo="http://purl.org/ontology/mo/" ' .
				'xmlns:skos="http://www.w3.org/2008/05/skos#" ' .
				'xmlns:time="http://www.w3.org/2006/time#" ' .
				'xmlns:dc="http://purl.org/dc/elements/1.1/" ' .
				'xmlns:dcterms="http://purl.org/dc/terms/" ' .
				'xmlns:wgs84_pos="http://www.w3.org/2003/01/geo/wgs84_pos#" ' .
				'xmlns:timeline="http://purl.org/NET/c4dm/timeline.owl#" ' .
				'xmlns:event="http://purl.org/NET/c4dm/event.owl#" ' .
				'xmlns:ma="http://www.w3.org/ns/ma-ont">');
		
		writeLn();
		writeLn('<rdf:Description rdf:about="' . _e($uri) . '.rdf">');
		writeLn('<rdfs:label>Description of the version ' . _e($this->title) . ' of the episode ' . _e($this->episode->title) . '</rdfs:label>');
		writeLn('<dcterms:created rdf:datatype="http://www.w3.org/2001/XMLSchema#dateTime">' . strftime('%Y-%m-%dT%H:%M:%SZ', parse_datetime($this->object->created)) . '</dcterms:created>');
		writeLn('<dcterms:modified rdf:datatype="http://www.w3.org/2001/XMLSchema#dateTime">' . strftime('%Y-%m-%dT%H:%M:%SZ', parse_datetime($this->object->modified)) . '</dcterms:modified>');
		writeLn('<foaf:primaryTopic rdf:resource="' . _e($uri . '#version') . '" />');
		writeLn('</rdf:Description>');
		writeLn();
		
		writeLn('<po:Version rdf:resource="' . _e($uri . '#version') . '">');
		writeLn('<po:pid>' . _e($this->object->uuid) . '</po:pid>');
		writeLn('<rdfs:label>A version of ' . _e($this->episode->title) . '</rdfs:label>');
		writeLn('</po:Version>');
		writeLn();

		writeLn('<po:Episode rdf:about="' . _e($this->request->root . $this->episode->relativeURI. '#episode') . '">');
		writeLn('<rdfs:label>' . _e($this->episode->title) . '</rdfs:label>');
		writeLn('<po:version rdf:resource="' . _e($uri . '#version') . '" />');
		writeLn('</po:Episode>');
		writeLn();

		writeLn('<po:Availability>');
		foreach($this->object->resources as $loc)
		{
			if(empty($loc->available)) continue;
			if(!empty($loc->offline)) continue;
			writeLn('<po:media_item>');
			writeLn('<po:MediaItem rdf:about="' . _e($uri . '#' . $loc->uuid) . '">');
			writeLn('<po:pid>' . _e($loc->uuid) . '</po:pid>');
			writeLn('<ma:locator rdf:resource="'. _e($loc->uri) . '" />');
			writeLn('</po:MediaItem>');
			writeLn('</po:media_item>');
		}
		writeLn('</po:Availability>');
		writeLn();
		writeLn('</rdf:RDF>');
	}
}
