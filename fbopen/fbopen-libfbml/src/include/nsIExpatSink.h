/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM nsIExpatSink.idl
 */

#ifndef __gen_nsIExpatSink_h__
#define __gen_nsIExpatSink_h__


#ifndef __gen_nsISupports_h__
#include "nsISupports.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif

/* starting interface:    nsIExpatSink */
#define NS_IEXPATSINK_IID_STR "1deea160-c661-11d5-84cc-0010a4e0c706"

#define NS_IEXPATSINK_IID \
  {0x1deea160, 0xc661, 0x11d5, \
    { 0x84, 0xcc, 0x00, 0x10, 0xa4, 0xe0, 0xc7, 0x06 }}

/**
 * This interface should be implemented by any content sink that wants
 * to get output from expat and do something with it; in other words,
 * by any sink that handles some sort of XML dialect.
 */
class NS_NO_VTABLE nsIExpatSink : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IEXPATSINK_IID)

  /**
   * Called to handle the opening tag of an element.
   * @param aName the fully qualified tagname of the element
   * @param aAtts the array of attribute names and values.  There are
   *        aAttsCount/2 names and aAttsCount/2 values, so the total number of
   *        elements in the array is aAttsCount.  The names and values
   *        alternate.  Thus, if we number attributes starting with 0,
   *        aAtts[2*k] is the name of the k-th attribute and aAtts[2*k+1] is
   *        the value of that attribute  Both explicitly specified attributes
   *        and attributes that are defined to have default values in a DTD are
   *        present in aAtts.
   * @param aAttsCount the number of elements in aAtts.
   * @param aIndex If the element has an attribute of type ID, then
   *        aAtts[aIndex] is the name of that attribute.  Otherwise, aIndex
   *        is -1
   * @param aLineNumber the line number of the start tag in the data stream.
   */
  /* void HandleStartElement (in wstring aName, [array, size_is (aAttsCount)] in wstring aAtts, in unsigned long aAttsCount, in long aIndex, in unsigned long aLineNumber); */
  NS_IMETHOD HandleStartElement(const PRUnichar *aName, const PRUnichar **aAtts, PRUint32 aAttsCount, PRInt32 aIndex, PRUint32 aLineNumber) = 0;

  /**
   * Called to handle the closing tag of an element.
   * @param aName the fully qualified tagname of the element
   */
  /* void HandleEndElement (in wstring aName); */
  NS_IMETHOD HandleEndElement(const PRUnichar *aName) = 0;

  /**
   * Called to handle a comment
   * @param aCommentText the text of the comment (not including the
   *        "<!--" and "-->")
   */
  /* void HandleComment (in wstring aCommentText); */
  NS_IMETHOD HandleComment(const PRUnichar *aCommentText) = 0;

  /**
   * Called to handle a CDATA section
   * @param aData the text in the CDATA section.  This is null-terminated.
   * @param aLength the length of the aData string
   */
  /* void HandleCDataSection ([size_is (aLength)] in wstring aData, in unsigned long aLength); */
  NS_IMETHOD HandleCDataSection(const PRUnichar *aData, PRUint32 aLength) = 0;

  /**
   * Called to handle the doctype declaration
   */
  /* void HandleDoctypeDecl (in AString aSubset, in AString aName, in AString aSystemId, in AString aPublicId, in nsISupports aCatalogData); */
  NS_IMETHOD HandleDoctypeDecl(const nsAString & aSubset, const nsAString & aName, const nsAString & aSystemId, const nsAString & aPublicId, nsISupports *aCatalogData) = 0;

  /**
   * Called to handle character data.  Note that this does NOT get
   * called for the contents of CDATA sections.
   * @param aData the data to handle.  aData is NOT NULL-TERMINATED.
   * @param aLength the length of the aData string
   */
  /* void HandleCharacterData ([size_is (aLength)] in wstring aData, in unsigned long aLength); */
  NS_IMETHOD HandleCharacterData(const PRUnichar *aData, PRUint32 aLength) = 0;

  /**
   * Called to handle a processing instruction
   * @param aTarget the PI target (e.g. xml-stylesheet)
   * @param aData all the rest of the data in the PI
   */
  /* void HandleProcessingInstruction (in wstring aTarget, in wstring aData); */
  NS_IMETHOD HandleProcessingInstruction(const PRUnichar *aTarget, const PRUnichar *aData) = 0;

  /**
   * Handle the XML Declaration.
   *
   * @param aVersion    The version string, can be null if not specified.
   * @param aEncoding   The encoding string, can be null if not specified.
   * @param aStandalone -1, 0, or 1 indicating respectively that there was no
   *                    standalone parameter in the declaration, that it was
   *                    given as no, or that it was given as yes.
   */
  /* void HandleXMLDeclaration (in wstring aVersion, in wstring aEncoding, in long aStandalone); */
  NS_IMETHOD HandleXMLDeclaration(const PRUnichar *aVersion, const PRUnichar *aEncoding, PRInt32 aStandalone) = 0;

  /* void ReportError (in wstring aErrorText, in wstring aSourceText); */
  NS_IMETHOD ReportError(const PRUnichar *aErrorText, const PRUnichar *aSourceText) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIEXPATSINK \
  NS_IMETHOD HandleStartElement(const PRUnichar *aName, const PRUnichar **aAtts, PRUint32 aAttsCount, PRInt32 aIndex, PRUint32 aLineNumber); \
  NS_IMETHOD HandleEndElement(const PRUnichar *aName); \
  NS_IMETHOD HandleComment(const PRUnichar *aCommentText); \
  NS_IMETHOD HandleCDataSection(const PRUnichar *aData, PRUint32 aLength); \
  NS_IMETHOD HandleDoctypeDecl(const nsAString & aSubset, const nsAString & aName, const nsAString & aSystemId, const nsAString & aPublicId, nsISupports *aCatalogData); \
  NS_IMETHOD HandleCharacterData(const PRUnichar *aData, PRUint32 aLength); \
  NS_IMETHOD HandleProcessingInstruction(const PRUnichar *aTarget, const PRUnichar *aData); \
  NS_IMETHOD HandleXMLDeclaration(const PRUnichar *aVersion, const PRUnichar *aEncoding, PRInt32 aStandalone); \
  NS_IMETHOD ReportError(const PRUnichar *aErrorText, const PRUnichar *aSourceText); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIEXPATSINK(_to) \
  NS_IMETHOD HandleStartElement(const PRUnichar *aName, const PRUnichar **aAtts, PRUint32 aAttsCount, PRInt32 aIndex, PRUint32 aLineNumber) { return _to HandleStartElement(aName, aAtts, aAttsCount, aIndex, aLineNumber); } \
  NS_IMETHOD HandleEndElement(const PRUnichar *aName) { return _to HandleEndElement(aName); } \
  NS_IMETHOD HandleComment(const PRUnichar *aCommentText) { return _to HandleComment(aCommentText); } \
  NS_IMETHOD HandleCDataSection(const PRUnichar *aData, PRUint32 aLength) { return _to HandleCDataSection(aData, aLength); } \
  NS_IMETHOD HandleDoctypeDecl(const nsAString & aSubset, const nsAString & aName, const nsAString & aSystemId, const nsAString & aPublicId, nsISupports *aCatalogData) { return _to HandleDoctypeDecl(aSubset, aName, aSystemId, aPublicId, aCatalogData); } \
  NS_IMETHOD HandleCharacterData(const PRUnichar *aData, PRUint32 aLength) { return _to HandleCharacterData(aData, aLength); } \
  NS_IMETHOD HandleProcessingInstruction(const PRUnichar *aTarget, const PRUnichar *aData) { return _to HandleProcessingInstruction(aTarget, aData); } \
  NS_IMETHOD HandleXMLDeclaration(const PRUnichar *aVersion, const PRUnichar *aEncoding, PRInt32 aStandalone) { return _to HandleXMLDeclaration(aVersion, aEncoding, aStandalone); } \
  NS_IMETHOD ReportError(const PRUnichar *aErrorText, const PRUnichar *aSourceText) { return _to ReportError(aErrorText, aSourceText); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIEXPATSINK(_to) \
  NS_IMETHOD HandleStartElement(const PRUnichar *aName, const PRUnichar **aAtts, PRUint32 aAttsCount, PRInt32 aIndex, PRUint32 aLineNumber) { return !_to ? NS_ERROR_NULL_POINTER : _to->HandleStartElement(aName, aAtts, aAttsCount, aIndex, aLineNumber); } \
  NS_IMETHOD HandleEndElement(const PRUnichar *aName) { return !_to ? NS_ERROR_NULL_POINTER : _to->HandleEndElement(aName); } \
  NS_IMETHOD HandleComment(const PRUnichar *aCommentText) { return !_to ? NS_ERROR_NULL_POINTER : _to->HandleComment(aCommentText); } \
  NS_IMETHOD HandleCDataSection(const PRUnichar *aData, PRUint32 aLength) { return !_to ? NS_ERROR_NULL_POINTER : _to->HandleCDataSection(aData, aLength); } \
  NS_IMETHOD HandleDoctypeDecl(const nsAString & aSubset, const nsAString & aName, const nsAString & aSystemId, const nsAString & aPublicId, nsISupports *aCatalogData) { return !_to ? NS_ERROR_NULL_POINTER : _to->HandleDoctypeDecl(aSubset, aName, aSystemId, aPublicId, aCatalogData); } \
  NS_IMETHOD HandleCharacterData(const PRUnichar *aData, PRUint32 aLength) { return !_to ? NS_ERROR_NULL_POINTER : _to->HandleCharacterData(aData, aLength); } \
  NS_IMETHOD HandleProcessingInstruction(const PRUnichar *aTarget, const PRUnichar *aData) { return !_to ? NS_ERROR_NULL_POINTER : _to->HandleProcessingInstruction(aTarget, aData); } \
  NS_IMETHOD HandleXMLDeclaration(const PRUnichar *aVersion, const PRUnichar *aEncoding, PRInt32 aStandalone) { return !_to ? NS_ERROR_NULL_POINTER : _to->HandleXMLDeclaration(aVersion, aEncoding, aStandalone); } \
  NS_IMETHOD ReportError(const PRUnichar *aErrorText, const PRUnichar *aSourceText) { return !_to ? NS_ERROR_NULL_POINTER : _to->ReportError(aErrorText, aSourceText); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsExpatSink : public nsIExpatSink
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIEXPATSINK

  nsExpatSink();

private:
  ~nsExpatSink();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsExpatSink, nsIExpatSink)

nsExpatSink::nsExpatSink()
{
  /* member initializers and constructor code */
}

nsExpatSink::~nsExpatSink()
{
  /* destructor code */
}

/* void HandleStartElement (in wstring aName, [array, size_is (aAttsCount)] in wstring aAtts, in unsigned long aAttsCount, in long aIndex, in unsigned long aLineNumber); */
NS_IMETHODIMP nsExpatSink::HandleStartElement(const PRUnichar *aName, const PRUnichar **aAtts, PRUint32 aAttsCount, PRInt32 aIndex, PRUint32 aLineNumber)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void HandleEndElement (in wstring aName); */
NS_IMETHODIMP nsExpatSink::HandleEndElement(const PRUnichar *aName)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void HandleComment (in wstring aCommentText); */
NS_IMETHODIMP nsExpatSink::HandleComment(const PRUnichar *aCommentText)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void HandleCDataSection ([size_is (aLength)] in wstring aData, in unsigned long aLength); */
NS_IMETHODIMP nsExpatSink::HandleCDataSection(const PRUnichar *aData, PRUint32 aLength)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void HandleDoctypeDecl (in AString aSubset, in AString aName, in AString aSystemId, in AString aPublicId, in nsISupports aCatalogData); */
NS_IMETHODIMP nsExpatSink::HandleDoctypeDecl(const nsAString & aSubset, const nsAString & aName, const nsAString & aSystemId, const nsAString & aPublicId, nsISupports *aCatalogData)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void HandleCharacterData ([size_is (aLength)] in wstring aData, in unsigned long aLength); */
NS_IMETHODIMP nsExpatSink::HandleCharacterData(const PRUnichar *aData, PRUint32 aLength)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void HandleProcessingInstruction (in wstring aTarget, in wstring aData); */
NS_IMETHODIMP nsExpatSink::HandleProcessingInstruction(const PRUnichar *aTarget, const PRUnichar *aData)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void HandleXMLDeclaration (in wstring aVersion, in wstring aEncoding, in long aStandalone); */
NS_IMETHODIMP nsExpatSink::HandleXMLDeclaration(const PRUnichar *aVersion, const PRUnichar *aEncoding, PRInt32 aStandalone)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void ReportError (in wstring aErrorText, in wstring aSourceText); */
NS_IMETHODIMP nsExpatSink::ReportError(const PRUnichar *aErrorText, const PRUnichar *aSourceText)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_nsIExpatSink_h__ */
