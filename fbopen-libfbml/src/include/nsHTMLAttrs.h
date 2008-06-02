#ifndef nsHTMLAttrs_h___
#define nsHTMLAttrs_h___

#include "nsAString.h"

class nsIAtom;

enum nsHTMLAttr {
  /* this enum must be first and must be zero */
  eHTMLAttr_unknown = 0,
  eHTMLAttr_userdefined = 1
};

class nsHTMLAttrs {
public:
  static nsresult AddRefTable(void);
  static void ReleaseTable(void);

  static void RemoveExpandedAttr(int index);
  static void RemoveExpandedAttrs();
  static void AddAttr(int aEnum, const char *aAttrName);

  static nsHTMLAttr LookupAttr(const nsAString& aAttrName);
  static nsHTMLAttr CaseSensitiveLookupAttr(const PRUnichar* aAttrName);
  static const PRUnichar *GetStringValue(nsHTMLAttr aEnum);
  static nsIAtom *GetAtom(nsHTMLAttr aEnum);
};

#define eHTMLAttrs nsHTMLAttr

#endif /* nsHTMLAttrs_h___ */
