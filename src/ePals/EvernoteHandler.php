<?php

namespace ePals;



use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Notebook, EDAM\Types\Resource, EDAM\Types\ResourceAttributes,
  EDAM\NoteStore;
use EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;
use Evernote\Client;


//delvelopment key and secret
//define('OAUTH_CONSUMER_KEY', 'jiatq');
//define('OAUTH_CONSUMER_SECRET', 'c89ca981271a8b60');
//define('SANDBOX', TRUE);

class EvernoteHandler{
    
    protected $accessToken ;
    protected $client ;
    protected $noteStore ;
    protected $developClient ;
    
    function __construct($accessToken) {
        
        $this->accessToken = $accessToken ;
        $this->client = new Client(array(
            'token' => $this->accessToken, //agent user token
            'sandbox' => true,
            'consumerKey' => 'jiatq', //developer key
            'consumerSecret' => 'c89ca981271a8b60' //developer secret
        ));
        $this->noteStore = $this->client->getNoteStore();

    }
    
    //param: authorize success callback url
    public function authorize($callback){
        
        if(isset($callback)){
            $requestTokenInfo = $this->client->getRequestToken($callback());
        }else{
            $requestTokenInfo = $this->client->getRequestToken(self::getSelfCallbackUrl());
        }
        if ($requestTokenInfo) {
            
            $_SESSION['requestToken'] = $requestTokenInfo['oauth_token'];
            $_SESSION['requestTokenSecret'] = $requestTokenInfo['oauth_token_secret'];

            $urlAuthorize = $this->client->getAuthorizeUrl($_SESSION['requestToken']);
            header("Location: ".$urlAuthorize); 
            exit ;
        } 
    }
    
    static function getSelfCallbackUrl()
    {
        $thisUrl = (empty($_SERVER['HTTPS'])) ? "http://" : "https://";
        $thisUrl .= $_SERVER['SERVER_NAME'];
        $thisUrl .= ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? "" : (":".$_SERVER['SERVER_PORT']);
        $thisUrl .= $_SERVER['SCRIPT_NAME'];
        $thisUrl .= '?action=callback';
        return $thisUrl;
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
