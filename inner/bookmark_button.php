<?php
$bookmark_page = bookmark_pages($_SESSION['SYSTEM']['VISITOR_ID']);
echo '<a href="/s/page_bookmark_add?p='.$this->id.'" class="bookmarks'.($bookmark_page[ $this->id ]?' selected':'').'" rel="nofollow">'.($bookmark_page[ $this->id ]?'Из закладок':'В закладки').'</a>';
?>
