/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM nsIMIMEInfo.idl
 */

#ifndef __gen_nsIMIMEInfo_h__
#define __gen_nsIMIMEInfo_h__


#ifndef __gen_nsISupports_h__
#include "nsISupports.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif
class nsIURI; /* forward declaration */

class nsIFile; /* forward declaration */

class nsIUTF8StringEnumerator; /* forward declaration */

typedef PRInt32 nsMIMEInfoHandleAction;


/* starting interface:    nsIMIMEInfo */
#define NS_IMIMEINFO_IID_STR "1448b42f-cf0d-466e-9a15-64e876ebe857"

#define NS_IMIMEINFO_IID \
  {0x1448b42f, 0xcf0d, 0x466e, \
    { 0x9a, 0x15, 0x64, 0xe8, 0x76, 0xeb, 0xe8, 0x57 }}

class NS_NO_VTABLE nsIMIMEInfo : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IMIMEINFO_IID)

  /**
     * Gives you an array of file types associated with this type.
     *
     * @return Number of elements in the array.
     * @return Array of extensions.
     */
  /* nsIUTF8StringEnumerator getFileExtensions (); */
  NS_IMETHOD GetFileExtensions(nsIUTF8StringEnumerator **_retval) = 0;

  /**
     * Set File Extensions. Input is a comma delimited list of extensions.
     */
  /* void setFileExtensions (in AUTF8String aExtensions); */
  NS_IMETHOD SetFileExtensions(const nsACString & aExtensions) = 0;

  /**
     * Returns whether or not the given extension is
     * associated with this MIME info.
     *
     * @return TRUE if the association exists. 
     */
  /* boolean extensionExists (in AUTF8String aExtension); */
  NS_IMETHOD ExtensionExists(const nsACString & aExtension, PRBool *_retval) = 0;

  /**
     * Append a given extension to the set of extensions
     */
  /* void appendExtension (in AUTF8String aExtension); */
  NS_IMETHOD AppendExtension(const nsACString & aExtension) = 0;

  /**
     * Returns the first extension association in
     * the internal set of extensions.
     *
     * @return The first extension.
     */
  /* attribute AUTF8String primaryExtension; */
  NS_IMETHOD GetPrimaryExtension(nsACString & aPrimaryExtension) = 0;
  NS_IMETHOD SetPrimaryExtension(const nsACString & aPrimaryExtension) = 0;

  /**
     * The MIME type of this MIMEInfo.
     * 
     * @return String representing the MIME type.
     */
  /* readonly attribute ACString MIMEType; */
  NS_IMETHOD GetMIMEType(nsACString & aMIMEType) = 0;

  /**
     * A human readable description of the MIME info.
     *
     * @return The description
     */
  /* attribute AString description; */
  NS_IMETHOD GetDescription(nsAString & aDescription) = 0;
  NS_IMETHOD SetDescription(const nsAString & aDescription) = 0;

  /**
     * Mac Type and creator types
     */
  /* attribute PRUint32 macType; */
  NS_IMETHOD GetMacType(PRUint32 *aMacType) = 0;
  NS_IMETHOD SetMacType(PRUint32 aMacType) = 0;

  /* attribute PRUint32 macCreator; */
  NS_IMETHOD GetMacCreator(PRUint32 *aMacCreator) = 0;
  NS_IMETHOD SetMacCreator(PRUint32 aMacCreator) = 0;

  /**
     * Returns whether or not these two MIME infos are logically
     * equivalent maintaining the one-to-many relationship between
     * MIME types and file extensions.
     *
     * @returns TRUE if the two are considered equal
     */
  /* boolean equals (in nsIMIMEInfo aMIMEInfo); */
  NS_IMETHOD Equals(nsIMIMEInfo *aMIMEInfo, PRBool *_retval) = 0;

  /**
     * Returns a nsIFile that points to the application the user has said
     * they want associated with this content type. This is not always
     * guaranteed to be set!!
     */
  /* attribute nsIFile preferredApplicationHandler; */
  NS_IMETHOD GetPreferredApplicationHandler(nsIFile * *aPreferredApplicationHandler) = 0;
  NS_IMETHOD SetPreferredApplicationHandler(nsIFile * aPreferredApplicationHandler) = 0;

  /**
     * A pretty name description of the preferred application.
     */
  /* attribute AString applicationDescription; */
  NS_IMETHOD GetApplicationDescription(nsAString & aApplicationDescription) = 0;
  NS_IMETHOD SetApplicationDescription(const nsAString & aApplicationDescription) = 0;

  /**
     * Indicates whether a default application handler exists,
     * i.e. whether launchWithFile with action = useSystemDefault is possible
     * and applicationDescription will contain usable information.
     */
  /* readonly attribute boolean hasDefaultHandler; */
  NS_IMETHOD GetHasDefaultHandler(PRBool *aHasDefaultHandler) = 0;

  /**
     * A pretty name description of the associated default application. Only
     * usable if hasDefaultHandler is true.
     */
  /* readonly attribute AString defaultDescription; */
  NS_IMETHOD GetDefaultDescription(nsAString & aDefaultDescription) = 0;

  /**
     * Launches the application with the specified file, in a way that
     * depends on the value of preferredAction. preferredAction must be
     * useHelperApp or useSystemDefault.
     *
     * @param aFile The file to launch this application with.
     *
     * @throw NS_ERROR_INVALID_ARG if action is not valid for this function.
     * Other exceptions may be thrown.
     */
  /* void launchWithFile (in nsIFile aFile); */
  NS_IMETHOD LaunchWithFile(nsIFile *aFile) = 0;

  enum { saveToDisk = 0 };

  enum { alwaysAsk = 1 };

  enum { useHelperApp = 2 };

  enum { handleInternally = 3 };

  enum { useSystemDefault = 4 };

  /**
     * preferredAction is how the user specified they would like to handle
     * this content type: save to disk, use specified helper app, use OS
     * default handler or handle using navigator.
     */
  /* attribute nsMIMEInfoHandleAction preferredAction; */
  NS_IMETHOD GetPreferredAction(nsMIMEInfoHandleAction *aPreferredAction) = 0;
  NS_IMETHOD SetPreferredAction(nsMIMEInfoHandleAction aPreferredAction) = 0;

  /**
     * alwaysAskBeforeHandling: if true, we should always give the user a
     * dialog asking how to dispose of this content.
     */
  /* attribute boolean alwaysAskBeforeHandling; */
  NS_IMETHOD GetAlwaysAskBeforeHandling(PRBool *aAlwaysAskBeforeHandling) = 0;
  NS_IMETHOD SetAlwaysAskBeforeHandling(PRBool aAlwaysAskBeforeHandling) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIMIMEINFO \
  NS_IMETHOD GetFileExtensions(nsIUTF8StringEnumerator **_retval); \
  NS_IMETHOD SetFileExtensions(const nsACString & aExtensions); \
  NS_IMETHOD ExtensionExists(const nsACString & aExtension, PRBool *_retval); \
  NS_IMETHOD AppendExtension(const nsACString & aExtension); \
  NS_IMETHOD GetPrimaryExtension(nsACString & aPrimaryExtension); \
  NS_IMETHOD SetPrimaryExtension(const nsACString & aPrimaryExtension); \
  NS_IMETHOD GetMIMEType(nsACString & aMIMEType); \
  NS_IMETHOD GetDescription(nsAString & aDescription); \
  NS_IMETHOD SetDescription(const nsAString & aDescription); \
  NS_IMETHOD GetMacType(PRUint32 *aMacType); \
  NS_IMETHOD SetMacType(PRUint32 aMacType); \
  NS_IMETHOD GetMacCreator(PRUint32 *aMacCreator); \
  NS_IMETHOD SetMacCreator(PRUint32 aMacCreator); \
  NS_IMETHOD Equals(nsIMIMEInfo *aMIMEInfo, PRBool *_retval); \
  NS_IMETHOD GetPreferredApplicationHandler(nsIFile * *aPreferredApplicationHandler); \
  NS_IMETHOD SetPreferredApplicationHandler(nsIFile * aPreferredApplicationHandler); \
  NS_IMETHOD GetApplicationDescription(nsAString & aApplicationDescription); \
  NS_IMETHOD SetApplicationDescription(const nsAString & aApplicationDescription); \
  NS_IMETHOD GetHasDefaultHandler(PRBool *aHasDefaultHandler); \
  NS_IMETHOD GetDefaultDescription(nsAString & aDefaultDescription); \
  NS_IMETHOD LaunchWithFile(nsIFile *aFile); \
  NS_IMETHOD GetPreferredAction(nsMIMEInfoHandleAction *aPreferredAction); \
  NS_IMETHOD SetPreferredAction(nsMIMEInfoHandleAction aPreferredAction); \
  NS_IMETHOD GetAlwaysAskBeforeHandling(PRBool *aAlwaysAskBeforeHandling); \
  NS_IMETHOD SetAlwaysAskBeforeHandling(PRBool aAlwaysAskBeforeHandling); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIMIMEINFO(_to) \
  NS_IMETHOD GetFileExtensions(nsIUTF8StringEnumerator **_retval) { return _to GetFileExtensions(_retval); } \
  NS_IMETHOD SetFileExtensions(const nsACString & aExtensions) { return _to SetFileExtensions(aExtensions); } \
  NS_IMETHOD ExtensionExists(const nsACString & aExtension, PRBool *_retval) { return _to ExtensionExists(aExtension, _retval); } \
  NS_IMETHOD AppendExtension(const nsACString & aExtension) { return _to AppendExtension(aExtension); } \
  NS_IMETHOD GetPrimaryExtension(nsACString & aPrimaryExtension) { return _to GetPrimaryExtension(aPrimaryExtension); } \
  NS_IMETHOD SetPrimaryExtension(const nsACString & aPrimaryExtension) { return _to SetPrimaryExtension(aPrimaryExtension); } \
  NS_IMETHOD GetMIMEType(nsACString & aMIMEType) { return _to GetMIMEType(aMIMEType); } \
  NS_IMETHOD GetDescription(nsAString & aDescription) { return _to GetDescription(aDescription); } \
  NS_IMETHOD SetDescription(const nsAString & aDescription) { return _to SetDescription(aDescription); } \
  NS_IMETHOD GetMacType(PRUint32 *aMacType) { return _to GetMacType(aMacType); } \
  NS_IMETHOD SetMacType(PRUint32 aMacType) { return _to SetMacType(aMacType); } \
  NS_IMETHOD GetMacCreator(PRUint32 *aMacCreator) { return _to GetMacCreator(aMacCreator); } \
  NS_IMETHOD SetMacCreator(PRUint32 aMacCreator) { return _to SetMacCreator(aMacCreator); } \
  NS_IMETHOD Equals(nsIMIMEInfo *aMIMEInfo, PRBool *_retval) { return _to Equals(aMIMEInfo, _retval); } \
  NS_IMETHOD GetPreferredApplicationHandler(nsIFile * *aPreferredApplicationHandler) { return _to GetPreferredApplicationHandler(aPreferredApplicationHandler); } \
  NS_IMETHOD SetPreferredApplicationHandler(nsIFile * aPreferredApplicationHandler) { return _to SetPreferredApplicationHandler(aPreferredApplicationHandler); } \
  NS_IMETHOD GetApplicationDescription(nsAString & aApplicationDescription) { return _to GetApplicationDescription(aApplicationDescription); } \
  NS_IMETHOD SetApplicationDescription(const nsAString & aApplicationDescription) { return _to SetApplicationDescription(aApplicationDescription); } \
  NS_IMETHOD GetHasDefaultHandler(PRBool *aHasDefaultHandler) { return _to GetHasDefaultHandler(aHasDefaultHandler); } \
  NS_IMETHOD GetDefaultDescription(nsAString & aDefaultDescription) { return _to GetDefaultDescription(aDefaultDescription); } \
  NS_IMETHOD LaunchWithFile(nsIFile *aFile) { return _to LaunchWithFile(aFile); } \
  NS_IMETHOD GetPreferredAction(nsMIMEInfoHandleAction *aPreferredAction) { return _to GetPreferredAction(aPreferredAction); } \
  NS_IMETHOD SetPreferredAction(nsMIMEInfoHandleAction aPreferredAction) { return _to SetPreferredAction(aPreferredAction); } \
  NS_IMETHOD GetAlwaysAskBeforeHandling(PRBool *aAlwaysAskBeforeHandling) { return _to GetAlwaysAskBeforeHandling(aAlwaysAskBeforeHandling); } \
  NS_IMETHOD SetAlwaysAskBeforeHandling(PRBool aAlwaysAskBeforeHandling) { return _to SetAlwaysAskBeforeHandling(aAlwaysAskBeforeHandling); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIMIMEINFO(_to) \
  NS_IMETHOD GetFileExtensions(nsIUTF8StringEnumerator **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetFileExtensions(_retval); } \
  NS_IMETHOD SetFileExtensions(const nsACString & aExtensions) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetFileExtensions(aExtensions); } \
  NS_IMETHOD ExtensionExists(const nsACString & aExtension, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->ExtensionExists(aExtension, _retval); } \
  NS_IMETHOD AppendExtension(const nsACString & aExtension) { return !_to ? NS_ERROR_NULL_POINTER : _to->AppendExtension(aExtension); } \
  NS_IMETHOD GetPrimaryExtension(nsACString & aPrimaryExtension) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetPrimaryExtension(aPrimaryExtension); } \
  NS_IMETHOD SetPrimaryExtension(const nsACString & aPrimaryExtension) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetPrimaryExtension(aPrimaryExtension); } \
  NS_IMETHOD GetMIMEType(nsACString & aMIMEType) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetMIMEType(aMIMEType); } \
  NS_IMETHOD GetDescription(nsAString & aDescription) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetDescription(aDescription); } \
  NS_IMETHOD SetDescription(const nsAString & aDescription) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetDescription(aDescription); } \
  NS_IMETHOD GetMacType(PRUint32 *aMacType) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetMacType(aMacType); } \
  NS_IMETHOD SetMacType(PRUint32 aMacType) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetMacType(aMacType); } \
  NS_IMETHOD GetMacCreator(PRUint32 *aMacCreator) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetMacCreator(aMacCreator); } \
  NS_IMETHOD SetMacCreator(PRUint32 aMacCreator) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetMacCreator(aMacCreator); } \
  NS_IMETHOD Equals(nsIMIMEInfo *aMIMEInfo, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Equals(aMIMEInfo, _retval); } \
  NS_IMETHOD GetPreferredApplicationHandler(nsIFile * *aPreferredApplicationHandler) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetPreferredApplicationHandler(aPreferredApplicationHandler); } \
  NS_IMETHOD SetPreferredApplicationHandler(nsIFile * aPreferredApplicationHandler) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetPreferredApplicationHandler(aPreferredApplicationHandler); } \
  NS_IMETHOD GetApplicationDescription(nsAString & aApplicationDescription) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetApplicationDescription(aApplicationDescription); } \
  NS_IMETHOD SetApplicationDescription(const nsAString & aApplicationDescription) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetApplicationDescription(aApplicationDescription); } \
  NS_IMETHOD GetHasDefaultHandler(PRBool *aHasDefaultHandler) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetHasDefaultHandler(aHasDefaultHandler); } \
  NS_IMETHOD GetDefaultDescription(nsAString & aDefaultDescription) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetDefaultDescription(aDefaultDescription); } \
  NS_IMETHOD LaunchWithFile(nsIFile *aFile) { return !_to ? NS_ERROR_NULL_POINTER : _to->LaunchWithFile(aFile); } \
  NS_IMETHOD GetPreferredAction(nsMIMEInfoHandleAction *aPreferredAction) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetPreferredAction(aPreferredAction); } \
  NS_IMETHOD SetPreferredAction(nsMIMEInfoHandleAction aPreferredAction) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetPreferredAction(aPreferredAction); } \
  NS_IMETHOD GetAlwaysAskBeforeHandling(PRBool *aAlwaysAskBeforeHandling) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetAlwaysAskBeforeHandling(aAlwaysAskBeforeHandling); } \
  NS_IMETHOD SetAlwaysAskBeforeHandling(PRBool aAlwaysAskBeforeHandling) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetAlwaysAskBeforeHandling(aAlwaysAskBeforeHandling); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsMIMEInfo : public nsIMIMEInfo
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIMIMEINFO

  nsMIMEInfo();

private:
  ~nsMIMEInfo();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsMIMEInfo, nsIMIMEInfo)

nsMIMEInfo::nsMIMEInfo()
{
  /* member initializers and constructor code */
}

nsMIMEInfo::~nsMIMEInfo()
{
  /* destructor code */
}

/* nsIUTF8StringEnumerator getFileExtensions (); */
NS_IMETHODIMP nsMIMEInfo::GetFileExtensions(nsIUTF8StringEnumerator **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void setFileExtensions (in AUTF8String aExtensions); */
NS_IMETHODIMP nsMIMEInfo::SetFileExtensions(const nsACString & aExtensions)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* boolean extensionExists (in AUTF8String aExtension); */
NS_IMETHODIMP nsMIMEInfo::ExtensionExists(const nsACString & aExtension, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void appendExtension (in AUTF8String aExtension); */
NS_IMETHODIMP nsMIMEInfo::AppendExtension(const nsACString & aExtension)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute AUTF8String primaryExtension; */
NS_IMETHODIMP nsMIMEInfo::GetPrimaryExtension(nsACString & aPrimaryExtension)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsMIMEInfo::SetPrimaryExtension(const nsACString & aPrimaryExtension)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute ACString MIMEType; */
NS_IMETHODIMP nsMIMEInfo::GetMIMEType(nsACString & aMIMEType)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute AString description; */
NS_IMETHODIMP nsMIMEInfo::GetDescription(nsAString & aDescription)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsMIMEInfo::SetDescription(const nsAString & aDescription)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute PRUint32 macType; */
NS_IMETHODIMP nsMIMEInfo::GetMacType(PRUint32 *aMacType)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsMIMEInfo::SetMacType(PRUint32 aMacType)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute PRUint32 macCreator; */
NS_IMETHODIMP nsMIMEInfo::GetMacCreator(PRUint32 *aMacCreator)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsMIMEInfo::SetMacCreator(PRUint32 aMacCreator)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* boolean equals (in nsIMIMEInfo aMIMEInfo); */
NS_IMETHODIMP nsMIMEInfo::Equals(nsIMIMEInfo *aMIMEInfo, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute nsIFile preferredApplicationHandler; */
NS_IMETHODIMP nsMIMEInfo::GetPreferredApplicationHandler(nsIFile * *aPreferredApplicationHandler)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsMIMEInfo::SetPreferredApplicationHandler(nsIFile * aPreferredApplicationHandler)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute AString applicationDescription; */
NS_IMETHODIMP nsMIMEInfo::GetApplicationDescription(nsAString & aApplicationDescription)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsMIMEInfo::SetApplicationDescription(const nsAString & aApplicationDescription)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute boolean hasDefaultHandler; */
NS_IMETHODIMP nsMIMEInfo::GetHasDefaultHandler(PRBool *aHasDefaultHandler)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute AString defaultDescription; */
NS_IMETHODIMP nsMIMEInfo::GetDefaultDescription(nsAString & aDefaultDescription)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void launchWithFile (in nsIFile aFile); */
NS_IMETHODIMP nsMIMEInfo::LaunchWithFile(nsIFile *aFile)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute nsMIMEInfoHandleAction preferredAction; */
NS_IMETHODIMP nsMIMEInfo::GetPreferredAction(nsMIMEInfoHandleAction *aPreferredAction)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsMIMEInfo::SetPreferredAction(nsMIMEInfoHandleAction aPreferredAction)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute boolean alwaysAskBeforeHandling; */
NS_IMETHODIMP nsMIMEInfo::GetAlwaysAskBeforeHandling(PRBool *aAlwaysAskBeforeHandling)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsMIMEInfo::SetAlwaysAskBeforeHandling(PRBool aAlwaysAskBeforeHandling)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_nsIMIMEInfo_h__ */
