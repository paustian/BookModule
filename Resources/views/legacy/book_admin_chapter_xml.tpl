<!--(chapter)-->
<!--(chapname){$chapter.name}(/chapname)-->
<!--(chapid){$chapter.cid}(/chapid)-->
<!--(chapnumber){$chapter.number}(/chapnumber)-->
<!--(bookid){$chapter.bid}(/bookid)-->
{section loop=$articles name=i}
<!--(section)-->
<!--(arttitle){$articles[i].title}(/arttitle)-->
<!--(artchapid){$articles[i].cid}(/artchapid)-->
<!--(artbookid){$articles[i].bid}(/artbookid)-->
<!--(artcounter){$articles[i].counter}(/artcounter)-->
<!--(artlang){$articles[i].lang}(/artlang)-->
<!--(artnext){$articles[i].next}(/artnext)-->
<!--(artprev){$articles[i].prev}(/artprev)-->
<!--(artartid){$articles[i].aid}(/artartid)-->
<!--(artnumber){$articles[i].aid}(/artnumber)-->
<!--(content)-->
{$articles[i].contents}
<!--(/content)-->
<!--(/section)-->
{/section}
<!--(/chapter)-->