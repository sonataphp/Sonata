<?php
//  STFeed.php
//  Sonata/Foundation
//
// Copyright 2008 Anis uddin Ahmad <anisniit@gmail.com>
//
// Modified to fit Sonata Framework syntax standards by Roman Efimov <romefimov@gmail.com>
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//

class STFeedItem {
    //Collection of feed elements
    private $elements = array();
    private $version;
    
    /**
     * Constructor
     *
     * @param    contant     (RSS1/RSS2/ATOM) RSS2 is default.
     */
    public function __construct($version = RSS2) {
        $this->version = $version;
    }
    
    /**
     * Add an element to elements array
     *
     * @access   public
     * @param    string  The tag name of an element
     * @param    string  The content of tag
     * @param    array   Attributes(if any) in 'attrName' => 'attrValue' format
     * @return   void
     */
    public function addElement($elementName, $content, $attributes = null) {
        $this->elements[$elementName]['name'] = $elementName;
        $this->elements[$elementName]['content'] = $content;
        $this->elements[$elementName]['attributes'] = $attributes;
    }
    
    /**
     * Set multiple feed elements from an array.
     * Elements which have attributes cannot be added by this method
     *
     * @access   public
     * @param    array   array of elements in 'tagName' => 'tagContent' format.
     * @return   void
     */
    public function addElementArray($elementArray) {
        if (!is_array($elementArray))
            return;
        foreach ($elementArray as $elementName => $content) {
            $this->addElement($elementName, $content);
        }
    }
    
    /**
     * Return the collection of elements in this feed item
     *
     * @access   public
     * @return   array
     */
    public function getElements() {
        return $this->elements;
    }
    
    // Wrapper functions ------------------------------------------------------
    
    /**
     * Set the 'description' element of feed item
     *
     * @access   public
     * @param    string  The content of 'description' element
     * @return   void
     */
    public function setDescription($description) {
        $tag = ($this->version == ATOM) ? 'summary' : 'description';
        $this->addElement($tag, $description);
    }
    
    /**
     * @desc     Set the 'title' element of feed item
     * @access   public
     * @param    string  The content of 'title' element
     * @return   void
     */
    public function setTitle($title) {
        $this->addElement('title', $title);
    }
    
    /**
     * Set the 'date' element of feed item
     *
     * @access   public
     * @param    string  The content of 'date' element
     * @return   void
     */
    public function setDate($date) {
        if (!is_numeric($date)) {
            $date = strtotime($date);
        }
        
        if ($this->version == ATOM) {
            $tag = 'updated';
            $value = date(DATE_ATOM, $date);
        } elseif ($this->version == RSS2) {
            $tag = 'pubDate';
            $value = date(DATE_RSS, $date);
        } else {
            $tag = 'dc:date';
            $value = date("Y-m-d", $date);
        }
        
        $this->addElement($tag, $value);
    }
    
    /**
     * Set the 'link' element of feed item
     *
     * @access   public
     * @param    string  The content of 'link' element
     * @return   void
     */
    public function setLink($link) {
        if ($this->version == RSS2 || $this->version == RSS1) {
            $this->addElement('link', $link);
        } else {
            $this->addElement('link', '', array('href' => $link));
            $this->addElement('id', STFeedWriter::uuid($link, 'urn:uuid:'));
        }
    }
    
    /**
     * Set the 'encloser' element of feed item
     * For RSS 2.0 only
     *
     * @access   public
     * @param    string  The url attribute of encloser tag
     * @param    string  The length attribute of encloser tag
     * @param    string  The type attribute of encloser tag
     * @return   void
     */
    public function setEncloser($url, $length, $type) {
        $attributes = array('url' => $url, 'length' => $length, 'type' => $type);
        $this->addElement('enclosure', '', $attributes);
    }
}
// end of class STFeedItem

// RSS 0.90  Officially obsoleted by 1.0
// RSS 0.91, 0.92, 0.93 and 0.94  Officially obsoleted by 2.0
// So, define constants for RSS 1.0, RSS 2.0 and ATOM   

define('RSS1', 'RSS 1.0', true);
define('RSS2', 'RSS 2.0', true);
define('ATOM', 'ATOM', true);

class STFeedWriter extends STObject {
    // Collection of channel elements
    private $channels = array();
    // Collection of items as object of STFeedItem class.
    private $items = array();
    // Store some other version wise data
    private $data = array();
    // The tag names which have to encoded as CDATA
    private $CDATAEncoding = array();
    
    private $version = null;
    
    /**
     * Constructor
     *
     * @param    constant    the version constant (RSS1/RSS2/ATOM).
     */
    function __construct($version = RSS2) {
        $this->version = $version;
        
        // Setting default value for assential channel elements
        $this->channels['title'] = $version . ' Feed';
        $this->channels['link'] = '';
        
        //Tag names to encode in CDATA
        $this->CDATAEncoding = array('description', 'content:encoded', 'summary');
    }
    
    // Start # public functions ---------------------------------------------
    
    /**
     * Set a channel element
     * @access   public
     * @param    string  name of the channel tag
     * @param    string  content of the channel tag
     * @return   void
     */
    public function setChannelElement($elementName, $content) {
        $this->channels[$elementName] = $content;
    }
    
    /**
     * Set multiple channel elements from an array. Array elements
     * should be 'channelName' => 'channelContent' format.
     *
     * @access   public
     * @param    array   array of channels
     * @return   void
     */
    public function setChannelElementsFromArray($elementArray) {
        if (!is_array($elementArray))
            return;
        foreach ($elementArray as $elementName => $content) {
            $this->setChannelElement($elementName, $content);
        }
    }
    
    /**
     * Genarate the actual RSS/ATOM file
     *
     * @access   public
     * @return   void
     */
    public function genarateFeed() {
        header("Content-type: text/xml");
        
        $this->printHead();
        $this->printChannels();
        $this->printItems();
        $this->printTale();
    }
    
    /**
     * Create a new STFeedItem.
     *
     * @access   public
     * @return   object  instance of STFeedItem class
     */
    public function createNewItem() {
        $Item = new STFeedItem($this->version);
        return $Item;
    }
    
    /**
     * Add a STFeedItem to the main class
     *
     * @access   public
     * @param    object  instance of STFeedItem class
     * @return   void
     */
    public function addItem($feedItem) {
        $this->items[] = $feedItem;
    }
    
    
    // Wrapper functions -------------------------------------------------------------------
    
    /**
     * Set the 'title' channel element
     *
     * @access   public
     * @param    string  value of 'title' channel tag
     * @return   void
     */
    public function setTitle($title) {
        $this->setChannelElement('title', $title);
    }
    
    /**
     * Set the 'description' channel element
     *
     * @access   public
     * @param    string  value of 'description' channel tag
     * @return   void
     */
    public function setDescription($desciption) {
        $this->setChannelElement('description', $desciption);
    }
    
    /**
     * Set the 'link' channel element
     *
     * @access   public
     * @param    string  value of 'link' channel tag
     * @return   void
     */
    public function setLink($link) {
        $this->setChannelElement('link', $link);
    }
    
    /**
     * Set the 'image' channel element
     *
     * @access   public
     * @param    string  title of image
     * @param    string  link url of the image
     * @param    string  path url of the image
     * @return   void
     */
    public function setImage($title, $link, $url) {
        $this->setChannelElement('image', array('title' => $title, 'link' => $link, 'url' => $url));
    }
    
    /**
     * Set the 'about' channel element. Only for RSS 1.0
     *
     * @access   public
     * @param    string  value of 'about' channel tag
     * @return   void
     */
    public function setChannelAbout($url) {
        $this->data['ChannelAbout'] = $url;
    }
    
    /**
     * Genarates an UUID
     * @author     Anis uddin Ahmad <admin@ajaxray.com>
     * @param      string  an optional prefix
     * @return     string  the formated uuid
     */
    public function uuid($key = null, $prefix = '') {
        $key = ($key == null) ? uniqid(rand()) : $key;
        $chars = md5($key);
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);
        
        return $prefix . $uuid;
    }
    // End # public functions ----------------------------------------------
    
    // Start # private functions ----------------------------------------------
    
    /**
     * Prints the xml and rss namespace
     *
     * @access   private
     * @return   void
     */
    private function printHead() {
        $out = '<?phpxml version="1.0" encoding="utf-8"?>' . "\n";
        
        if ($this->version == RSS2) {
            $out .= '<rss version="2.0"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        >' . PHP_EOL;
        } elseif ($this->version == RSS1) {
            $out .= '<rdf:RDF 
         xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns="http://purl.org/rss/1.0/"
         xmlns:dc="http://purl.org/dc/elements/1.1/"
        >' . PHP_EOL;
        } elseif ($this->version == ATOM) {
            $out .= '<feed xmlns="http://www.w3.org/2005/Atom">' . PHP_EOL;
        }
        echo $out;
    }
    
    /**
     * Closes the open tags at the end of file
     *
     * @access   private
     * @return   void
     */
    private function printTale() {
        if ($this->version == RSS2) {
            echo '</channel>' . PHP_EOL . '</rss>';
        } elseif ($this->version == RSS1) {
            echo '</rdf:RDF>';
        } elseif ($this->version == ATOM) {
            echo '</feed>';
        }
    }
    
    /**
     * Creates a single node as xml format
     *
     * @access   private
     * @param    string  name of the tag
     * @param    mixed   tag value as string or array of nested tags in 'tagName' => 'tagValue' format
     * @param    array   Attributes(if any) in 'attrName' => 'attrValue' format
     * @return   string  formatted xml tag
     */
    private function makeNode($tagName, $tagContent, $attributes = null) {
        $nodeText = '';
        $attrText = '';
        
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $attrText .= " $key=\"$value\" ";
            }
        }
        
        if (is_array($tagContent) && $this->version == RSS1) {
            $attrText = ' rdf:parseType="Resource"';
        }
        
        
        $attrText .= (in_array($tagName, $this->CDATAEncoding) && $this->version == ATOM) ? ' type="html" ' : '';
        $nodeText .= (in_array($tagName, $this->CDATAEncoding)) ? "<{$tagName}{$attrText}><![CDATA[" : "<{$tagName}{$attrText}>";
        
        if (is_array($tagContent)) {
            foreach ($tagContent as $key => $value) {
                $nodeText .= $this->makeNode($key, $value);
            }
        } else {
            $nodeText .= (in_array($tagName, $this->CDATAEncoding)) ? $tagContent : htmlentities($tagContent);
        }
        
        $nodeText .= (in_array($tagName, $this->CDATAEncoding)) ? "]]></$tagName>" : "</$tagName>";
        
        return $nodeText . PHP_EOL;
    }
    
    /**
     * @desc     Print channels
     * @access   private
     * @return   void
     */
    private function printChannels() {
        //Start channel tag
        switch ($this->version) {
            case RSS2:
                echo '<channel>' . PHP_EOL;
                break;
            case RSS1:
                echo(isset($this->data['ChannelAbout'])) ? "<channel rdf:about=\"{$this->data['ChannelAbout']}\">" : "<channel rdf:about=\"{$this->channels['link']}\">";
                break;
        }
        
        //Print Items of channel
        foreach ($this->channels as $key => $value) {
            if ($this->version == ATOM && $key == 'link') {
                // ATOM prints link element as href attribute
                echo $this->makeNode($key, '', array('href' => $value));
                //Add the id for ATOM
                echo $this->makeNode('id', $this->uuid($value, 'urn:uuid:'));
            } else {
                echo $this->makeNode($key, $value);
            }
        }
        
        //RSS 1.0 have special tag <rdf:Seq> with channel 
        if ($this->version == RSS1) {
            echo "<items>" . PHP_EOL . "<rdf:Seq>" . PHP_EOL;
            foreach ($this->items as $item) {
                $thisItems = $item->getElements();
                echo "<rdf:li resource=\"{$thisItems['link']['content']}\"/>" . PHP_EOL;
            }
            echo "</rdf:Seq>" . PHP_EOL . "</items>" . PHP_EOL . "</channel>" . PHP_EOL;
        }
    }
    
    /**
     * Prints formatted feed items
     *
     * @access   private
     * @return   void
     */
    private function printItems() {
        foreach ($this->items as $item) {
            $thisItems = $item->getElements();
            
            //the argument is printed as rdf:about attribute of item in rss 1.0 
            echo $this->startItem($thisItems['link']['content']);
            
            foreach ($thisItems as $STFeedItem) {
                echo $this->makeNode($STFeedItem['name'], $STFeedItem['content'], $STFeedItem['attributes']);
            }
            echo $this->endItem();
        }
    }
    
    /**
     * Make the starting tag of channels
     *
     * @access   private
     * @param    string  The vale of about tag which is used for only RSS 1.0
     * @return   void
     */
    private function startItem($about = false) {
        if ($this->version == RSS2) {
            echo '<item>' . PHP_EOL;
        } elseif ($this->version == RSS1) {
            if ($about) {
                echo "<item rdf:about=\"$about\">" . PHP_EOL;
            } else {
                die('link element is not set .\n It\'s required for RSS 1.0 to be used as about attribute of item');
            }
        } elseif ($this->version == ATOM) {
            echo "<entry>" . PHP_EOL;
        }
    }
    
    /**
     * Closes feed item tag
     *
     * @access   private
     * @return   void
     */
    private function endItem() {
        if ($this->version == RSS2 || $this->version == RSS1) {
            echo '</item>' . PHP_EOL;
        } elseif ($this->version == ATOM) {
            echo "</entry>" . PHP_EOL;
        }
    }
    
    
    
    // End # private functions ----------------------------------------------
    
} // end of class STSTFeedWriter
?>
