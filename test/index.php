<?php

	require_once './config.php';

	use Database\DB;
	$db = new DB();

    /**
     * Select WHERE 
     */
    $title = 'mr. coffeehead';
	$select = $db->select('*', 'fringe_shows', ['title = ?' => $title]);
	echo '<h1>Select</h1>';
	echo '<pre>';
	print_r($select);
	echo '</pre>';

    /**
     * Select all
     */
	$allResult = $db->selectAll('*', 'fringe_shows');

	echo '<h1>SelectAll</h1>';
	echo '<pre>';
	print_r($allResult);
	echo '</pre>';

    /**
     * Insert and print result
     */
    $values = [
        'title' => 'The Somnambulist',
        'description' => 'Wandering the dream world',
        'stars' => 5
    ];
    if(empty($db->select('*', 'fringe_shows', ['title = ?' => $values['title']]))){
        echo '<h1>Insert</h1>';
        $insert = $db->insert('fringe_shows', $values);
        if(!$insert){
            echo 'insert failed';
        } else {
            echo 'insert succeeded!';
        }
        $select = $db->select('*', 'fringe_shows', ['title = ?' => $values['title']]);
        echo '<pre>';
        print_r($select);
        echo '</pre>';
    }

    /**
     * Delete
     */
    echo '<h1>Delete</h1>';
    $title = 'The Somnambulist';
    $deleteQuery = $db->delete('fringe_shows', ['title = ?' => $title]);
    if($deleteQuery){
        echo 'Delete successful!';
    } else {
        echo 'Delete failed';
    }

    /**
     * Close session
     */
    $db->close();