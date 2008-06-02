#include "nsCRT.h"
#include "nsReadableUtils.h"
#include "plhash.h"
#include "nsString.h"
#include "nsStaticAtom.h"
#include "nsHTMLAttrs.h"

static PRInt32 gAttrTableRefCount;
static PLHashTable* gAttrTable;


PR_STATIC_CALLBACK(PLHashNumber)
HTMLAttrsHashCodeUCPtr(const void *key)
{
  const PRUnichar *str = (const PRUnichar *)key;

  return nsCRT::HashCode(str);
}

PR_STATIC_CALLBACK(PRIntn)
HTMLAttrsKeyCompareUCPtr(const void *key1, const void *key2)
{
  const PRUnichar *str1 = (const PRUnichar *)key1;
  const PRUnichar *str2 = (const PRUnichar *)key2;

  return nsCRT::strcmp(str1, str2) == 0;
}


static PRUint32 sMaxAttrNameLength;
#define NS_HTMLATTR_NAME_MAX_LENGTH 32

static bool kAttrUnicodeTableInited = false;
static const PRUnichar* kAttrUnicodeTable[512];

//static
void nsHTMLAttrs::RemoveExpandedAttr(int index) {
  void *oldAttr = (void*)kAttrUnicodeTable[index];
  if (oldAttr) {
    PL_HashTableRemove(gAttrTable, oldAttr);
    free(oldAttr);
    kAttrUnicodeTable[index] = NULL;
  }
}

void nsHTMLAttrs::RemoveExpandedAttrs() {
  if (!kAttrUnicodeTableInited) return;

  for (int index = 0; index <  sizeof(kAttrUnicodeTable)/sizeof(kAttrUnicodeTable[0]); index++) {
    RemoveExpandedAttr(index);
  }
}

void nsHTMLAttrs::AddAttr(int aEnum, const char *aAttrName) {
  if (!kAttrUnicodeTableInited) {
    memset(kAttrUnicodeTable, 0, sizeof(kAttrUnicodeTable));
    kAttrUnicodeTableInited = true;
  }

  int len = strlen(aAttrName);
  NS_ASSERTION(len <= NS_HTMLATTR_NAME_MAX_LENGTH, "bad tag");

  PRUnichar *buf = (PRUnichar*)malloc((NS_HTMLATTR_NAME_MAX_LENGTH + 1)
                                      * sizeof(PRUnichar));
  int i = 0;


  for (const char *p = aAttrName; *p; p++) {
    char ch = *p;
    if (ch <= 'Z' && ch >= 'A') ch |= 0x20;
    buf[i] = ch;
    if (++i >= NS_HTMLATTR_NAME_MAX_LENGTH - 1) break;
  }

  buf[i] = 0;

  int index = aEnum - eHTMLAttr_userdefined;
  if (index < 0 || index >= sizeof(kAttrUnicodeTable)/sizeof(kAttrUnicodeTable[0])) {
    free(buf);
    return;
  }

  // remove old tags that are maapped to the enum
  RemoveExpandedAttr(index);

  // add new tags
  PRUint32 tag = NS_PTR_TO_INT32(PL_HashTableLookupConst(gAttrTable, buf));
  if (tag == eHTMLAttr_unknown) {
    kAttrUnicodeTable[index] = buf;
    PL_HashTableAdd(gAttrTable, buf, NS_INT32_TO_PTR(aEnum));
  } else {
    free(buf);
  }

  if (len > sMaxAttrNameLength) {
    sMaxAttrNameLength = len;
  }
}

// static
nsresult
nsHTMLAttrs::AddRefTable(void)
{
  if (gAttrTableRefCount++ == 0) {
    NS_ASSERTION(!gAttrTable, "pre existing hash!");

    gAttrTable = PL_NewHashTable(64, HTMLAttrsHashCodeUCPtr,
                                HTMLAttrsKeyCompareUCPtr, PL_CompareValues,
                                nsnull, nsnull);
    NS_ENSURE_TRUE(gAttrTable, NS_ERROR_OUT_OF_MEMORY);
    
    //NS_ASSERTION(sMaxAttrNameLength == NS_HTMLTAG_NAME_MAX_LENGTH,
    //             "NS_HTMLTAG_NAME_MAX_LENGTH not set correctly!");

    // Fill in our static atom pointers
    //NS_RegisterStaticAtoms(kAttrAtoms_info, NS_ARRAY_LENGTH(kAttrAtoms_info));

  }

  return NS_OK;
}



// static
void
nsHTMLAttrs::ReleaseTable(void)
{
  if (0 == --gAttrTableRefCount) {
    if (gAttrTable) {
      // Nothing to delete/free in this table, just destroy the table.

      PL_HashTableDestroy(gAttrTable);

      gAttrTable = nsnull;
    }
  }
}

// static
nsHTMLAttr
nsHTMLAttrs::CaseSensitiveLookupAttr(const PRUnichar* aAttrName)
{
  NS_ASSERTION(gAttrTable, "no lookup table, needs addref");
  NS_ASSERTION(aAttrName, "null tagname!");

  PRUint32 tag = NS_PTR_TO_INT32(PL_HashTableLookupConst(gAttrTable, aAttrName));

  return tag == eHTMLAttr_unknown ? eHTMLAttr_userdefined : (nsHTMLAttr)tag;
}

// static
nsHTMLAttr
nsHTMLAttrs::LookupAttr(const nsAString& aAttrName)
{
  PRUint32 length = aAttrName.Length();

  if (length > sMaxAttrNameLength) {
    return eHTMLAttr_userdefined;
  }

  static PRUnichar buf[NS_HTMLATTR_NAME_MAX_LENGTH + 1];

  nsAString::const_iterator iter;
  PRUint32 i = 0;
  PRUnichar c;

  aAttrName.BeginReading(iter);

  // Fast lowercasing-while-copying of ASCII characters into a
  // PRUnichar buffer

  while (i < length) {
    c = *iter;

    if (c <= 'Z' && c >= 'A') {
      c |= 0x20; // Lowercase the ASCII character.
    }

    buf[i] = c; // Copy ASCII character.

    ++i;
    ++iter;
  }

  buf[i] = 0;

  return CaseSensitiveLookupAttr(buf);
}

// static
const PRUnichar *
nsHTMLAttrs::GetStringValue(nsHTMLAttr aEnum)
{
  return kAttrUnicodeTable[aEnum - 2];
}

