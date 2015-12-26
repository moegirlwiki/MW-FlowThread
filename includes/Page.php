<?php
namespace FlowThread;
class Page {
	public $pageid = 0;
	public $posts = null;
	
	public function __construct( $listOfPost ) {
		$this->posts = $listOfPost;
	}

	public static function newFromId( $id ) {
		$dbr = wfGetDB( DB_SLAVE );

		// Invalid ID
		if (!is_numeric($id) || $id == 0) {
			throw new Exception("Invalid ID");
		}

		$res = $dbr->select('FlowThread', Post::getRequiredColumns(), 
			array(
            'flowthread_pageid' => $id
        ));

		$comments = array();
		$lookup = array();
        
        foreach ($res as $row) {
            $post = Post::newFromDatabaseRow($row);
            $comments[] = $post;
            $lookup[$row->flowthread_id] = $post;
        }

        // Set parent directly, so no more database queries needed when access getParent()
        foreach ($comments as $post) {
        	if($post->parentid !== null)
        		$post->parent = $lookup[$post->parentid->getBin()];
        }

        return new self($comments);
	}

	public function erase() {
		$dbw = wfGetDB(DB_MASTER);
        foreach($this->posts as $post) {
            if($post->isValid())
                $post->eraseSilently($dbw);
        }
        $this->posts = array();
	}

}