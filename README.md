This is the Book module for the zikula cms. It allows the handling of large structured
sets of documents into Books, Chapters and Articles. There is lots of functionality in 
this module and at some point I really need to write some documentation for it. 

##Version 5.0
This version is compatible with Zikula 3.0.0 and greater. It is now in RC1 state and 
should work in production sites. More testing is needed, but let me know 
if you have an issues by posting issues at [github](https://github.com/paustian/BookModule/issues)
I have worked hard to make most features easy to understand, but I provide some brief notes on how to use them here.
##The Overall Structure of Book Module Documents
You write *Articles* in html, these are organized and arrangend into *Chapters*, and these are organized into a *Book*.
Having this sturcture focuses your efforts and enables easy navigation through the content. You can also enable Scribite 
and write your content in a rich text editor.
###Creating a Book
When you first begin using the module before you can do anything else, create the title of your book by choosing Book-> 
Create New Book. You can also edit the title of any book by choosing Book->Edit or Delete Book.
###Creating a Chapter
To create a chapter, choose Chapter->Create Chapter. In this form give the chapter a name and a number. Then choose the 
book that to which the chapter belongs. Once you have articles in a chapter, you can also Edit, Delete, Export, 
Search/Replace, or check URLS using the second command in the Chapter menu. The chapter can be export the chapter in
html if you want to then process it to some other format (for example a pdf or ebook). If you want to edit the chapter
and reimport it, export it as xml. 

You can also do a Search and Replace in a chapter using Search/Replace (the magnifying glass in the Edit chapter interface)
This is grep comppatible. There is a preview function that I high recommend you use first to verify your search/replace 
is going to give you the desired result. Once you are happy with the search/replace uncheck the preview checkbox and 
perform the search again to actually make the replacements.
###Creating an Article
To create an article, choose Article->Create New Article. Here you fill in the title, contents and other fields to
create the content. Html is expected in the contents area. To edit an article, choose Article->Edit Article. A similar
interface is created, but all the field are filled in with the content of the article. You can also add glossary terms
to an article from this interface. You can manually link to a glossary entry by wrapping a term in glossary tags like 
this: 

`<a class="glossary">term</a>`

You can also choose a to have the module automatically detect all terms and link to the glossary for each term that appears
in the text. It will only link to the first term found in each piece of text. You can also delete aritcles from the 
article edit interface

###Creating Figures
Figures are created separately from the content and then placed within. This is to allow the reuse of figure and to separate
figures from the rest of the code. To create a figure, choose Figure->Create New Figure. Each figure links to a Book, a 
Chapter, and a Figure Numnber. The appropriate html is wrapped around the figure based upon the extention that is placed
into the path field. The Book module supports gif, jpg, png, mov, canvas (for html5), and swf extentions. Each of the 
book_user_buildlink#.html.twig templates has descriptions on how to set up your files so that it will be rendered correctly
by the book module.

###Creating Glossary Entries
Glossary entries are created using the Glossary->Create Glossary function. The definition and term are entered. Created
terms can also be edited using Glossary->Edit Glossary. It is also possible to create an xml file of terms and then import
it into the Book module. Follow the xml formatting example. Readers of your book may also request that you add definitions
Use Glossary->Check for Student Definitions to find terms that need defining. Once you have defined them, you can have glossary
items rendered the linked page.

###Hooking functions to book
The Book Module supports the Hook Subscriber interface which allows you to hook into other functionality. Two popular 
choices are to hook quizzes to the end of article using the Quickcheck module and using Scribite to enable rich text
editors in much of the Book Module interface. To enable these, choose Hooks and drag Hook Providers from the right side
into the appropriate area on the left side. 
##Version 3.0
Fixed modurl calls
Fixed PnRedirect calls
Fixed LogUtil calls
Fixed SessionUtil::setVar for status message, use addStatusPopup instead
