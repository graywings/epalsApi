<?php

namespace ePals;
//require_once dirname(__DIR__).'/evernote-sdk-php-master/vendor/autoload.php';

//require_once __DIR__."/functions.php";


use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Notebook, EDAM\Types\Resource, EDAM\Types\ResourceAttributes,
  EDAM\NoteStore;
use EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;
use Evernote\Client;


class EvernoteHandler{
    
    protected $accessToken ;
    protected $client ;
    protected $noteStore ;
    
    function __construct($accessToken) {
        
        $this->accessToken = $accessToken ;
        $this->client = new Client(array(
            'token' => $this->accessToken,
            'sandbox' => true));
        $this->noteStore = $this->client->getNoteStore();
    }

    public function queryNote($filter,$offset = 0,$maxNotes = 100){

        if(!$filter){ 

            $filter = new NoteStore\NoteFilter();
        }
        //$resultSpec = new NoteStore\NotesMetadataResultSpec();
        $notelist = $this->noteStore->findNotes($this->accessToken,$filter,$offset,$maxNotes);
        return $notelist;
    }

    public function makeNote($noteTitle, $noteBody, $parentNotebook = null) {

        $nBody = '<?xml version="1.0" encoding="UTF-8"?>';
        $nBody .= '<!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml2.dtd">';
        $nBody .= '<en-note>' . $noteBody . '</en-note>';

        // Create note object
        $ourNote = new Note();
        $ourNote->title = $noteTitle;
        $ourNote->content = $nBody;

        // parentNotebook is optional; if omitted, default notebook is used
        if (isset($parentNotebook) && isset($parentNotebook->guid)) {
            $ourNote->notebookGuid = $parentNotebook->guid;
        }
        // Attempt to create note in Evernote account
        try {

            $note = $this->noteStore->createNote($this->accessToken,$ourNote);

        } catch (EDAMUserException $edue) {
            // Something was wrong with the note data
            // See EDAMErrorCode enumeration for error code explanation
            // http://dev.evernote.com/documentation/reference/Errors.html#Enum_EDAMErrorCode
            foreach ($GLOBALS['\EDAM\Error\E_EDAMErrorCode'] as $e => $v)
            {
                if($v == $edue->errorCode)
                {
                    return $e;
                }
            }
        } catch (EDAMNotFoundException $ednfe) {
            // Parent Notebook GUID doesn't correspond to an actual notebook
            return $ednfe;
        }

        // Return created note object
        return $note;

    }


    public function updateNote($noteTitle, $noteBody, $parentNotebook = null,$updateNoteGuid) {

        $nBody = '<?xml version="1.0" encoding="UTF-8"?>';
        $nBody .= '<!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml2.dtd">';
        $nBody .= '<en-note>' . $noteBody . '</en-note>';

        // Create note object
        $ourNote = new Note();
        $ourNote->guid = $updateNoteGuid;
        $ourNote->title = $noteTitle;
        $ourNote->content = $nBody;

        // parentNotebook is optional; if omitted, default notebook is used
        if (isset($parentNotebook) && isset($parentNotebook->guid)) {
            $ourNote->notebookGuid = $parentNotebook->guid;
        }

        // Attempt to create note in Evernote account
        try {

            $note = $this->noteStore->updateNote($this->accessToken,$ourNote);

        } catch (EDAMUserException $edue) {
            // Something was wrong with the note data
            // See EDAMErrorCode enumeration for error code explanation
            // http://dev.evernote.com/documentation/reference/Errors.html#Enum_EDAMErrorCode
            foreach ($GLOBALS['\EDAM\Error\E_EDAMErrorCode'] as $e => $v)
            {
                if($v == $edue->errorCode)
                {
                    return $e;
                }
            }
        } catch (EDAMNotFoundException $ednfe) {
            // Parent Notebook GUID doesn't correspond to an actual notebook
            return $edue;
        }

        return $note;

    }


    public function deleteNote($noteGuid) {

        $result = $this->noteStore->deleteNote($this->accessToken,$noteGuid);

        return $result;

    }


    //function of notebook

    public function queryNotebook(){

        $notebooks = $this->noteStore->listNotebooks($this->accessToken);

        return $notebooks;
    }

    public function makeNotebook($notebookName) {

        $notebook = new Notebook();
        $notebook->name = $notebookName;

        try {
            $new_notebook = $this->noteStore->createNotebook($this->accessToken, $notebook);
        } catch (EDAMUserException $edue) {
            // Something was wrong with the notebook data
            // See EDAMErrorCode enumeration for error code explanation
            // http://dev.evernote.com/documentation/reference/Errors.html#Enum_EDAMErrorCode
            //print "EDAMUserException: " . $edue->errorCode;
            //var_dump($GLOBALS['\EDAM\Error\E_EDAMErrorCode']);
            foreach ($GLOBALS['\EDAM\Error\E_EDAMErrorCode'] as $e => $v)
            {
                if($v == $edue->errorCode)
                {
                    return $e;
                }
            }
        } catch (EDAMNotFoundException $ednfe) {

            return $ednfe;
        }

        // Return created note object
        return $new_notebook;
    }

    public function editNotebook($notebookGuid, $notebookName) {
        
        $notebook = new Notebook();

        $notebook->guid = $notebookGuid;
        $notebook->name = $notebookName;

        try {
            $new_notebook = $this->noteStore->updateNotebook($this->accessToken, $notebook);
        } catch (EDAMUserException $edue) {
            // Something was wrong with the notebook data
            // See EDAMErrorCode enumeration for error code explanation
            // http://dev.evernote.com/documentation/reference/Errors.html#Enum_EDAMErrorCode
            //print "EDAMUserException: " . $edue;
            foreach ($GLOBALS['\EDAM\Error\E_EDAMErrorCode'] as $e => $v)
            {
                if($v == $edue->errorCode)
                {
                    return  $e;
                }
            }
        } catch (EDAMNotFoundException $ednfe) {
            return  $ednfe;
        }
        // Return created note object
        return $new_notebook;
    }

    public function expungeNotebook($notebookGuid) {

        $result = $this->noteStore->expungeNotebook($this->accessToken,$notebookGuid);

        return $result;
    }

}
?>
