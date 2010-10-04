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

uses('rdf');

require_once(MODULES_ROOT . 'media/model.php');

abstract class MediaBrowser extends Page
{	
	protected $modelClass = 'Media';
	
	protected function perform_GET_RDF()
	{
		$uri = $this->request->pageUri;
		if(strlen($uri) > 1 && substr($uri, -1) == '/') $uri = substr($uri, 0, -1);

		$doc = new RDFDocument($uri . '.rdf', $this->request->root . $this->object->__get('instanceRelativeURI'));
		$this->object->rdf($doc, $this->request);
		$this->request->header('Content-type', 'application/rdf+xml');
		$this->request->flush();
		$xml = $doc->asXML();
		if(is_array($xml))
		{
			writeLn(implode("\n", $xml));
		}
		else
		{
			writeLn($xml);
		}
	}
}
