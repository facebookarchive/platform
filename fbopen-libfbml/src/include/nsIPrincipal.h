/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM nsIPrincipal.idl
 */

#ifndef __gen_nsIPrincipal_h__
#define __gen_nsIPrincipal_h__


#ifndef __gen_nsISerializable_h__
#include "nsISerializable.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif
struct JSContext;
struct JSPrincipals;
class nsIURI; /* forward declaration */


/* starting interface:    nsIPrincipal */
#define NS_IPRINCIPAL_IID_STR "fb9ddeb9-26f9-46b8-85d5-3978aaee05aa"

#define NS_IPRINCIPAL_IID \
  {0xfb9ddeb9, 0x26f9, 0x46b8, \
    { 0x85, 0xd5, 0x39, 0x78, 0xaa, 0xee, 0x05, 0xaa }}

class NS_NO_VTABLE nsIPrincipal : public nsISerializable {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IPRINCIPAL_IID)

  /**
     * Values of capabilities for each principal. Order is
     * significant: if an operation is performed on a set
     * of capabilities, the minimum is computed.
     */
  enum { ENABLE_DENIED = 1 };

  enum { ENABLE_UNKNOWN = 2 };

  enum { ENABLE_WITH_USER_PERMISSION = 3 };

  enum { ENABLE_GRANTED = 4 };

  /**
     * Returns the security preferences associated with this principal.
     * prefBranch will be set to the pref branch to which these preferences
     * pertain.  id is a pseudo-unique identifier, pertaining to either the
     * fingerprint or the origin.  subjectName is a name that identifies the
     * entity this principal represents (may be empty).  grantedList and
     * deniedList are space-separated lists of capabilities which were
     * explicitly granted or denied by a pref.
     */
  /* void getPreferences (out string prefBranch, out string id, out string subjectName, out string grantedList, out string deniedList); */
  NS_IMETHOD GetPreferences(char **prefBranch, char **id, char **subjectName, char **grantedList, char **deniedList) = 0;

  /**
     * Returns whether the other principal is equivalent to this principal.
     * Principals are considered equal if they are the same principal,
     * they have the same origin, or have the same certificate fingerprint ID
     */
  /* boolean equals (in nsIPrincipal other); */
  NS_IMETHOD Equals(nsIPrincipal *other, PRBool *_retval) = 0;

  /**
     * Returns a hash value for the principal.
     */
  /* readonly attribute unsigned long hashValue; */
  NS_IMETHOD GetHashValue(PRUint32 *aHashValue) = 0;

  /**
     * Returns the JS equivalent of the principal.
     * @see JSPrincipals.h
     */
  /* JSPrincipals getJSPrincipals (in JSContext cx); */
  NS_IMETHOD GetJSPrincipals(JSContext * cx, JSPrincipals * *_retval) = 0;

  /**
     * The domain security policy of the principal.
     */
  /* attribute voidPtr securityPolicy; */
  NS_IMETHOD GetSecurityPolicy(void * *aSecurityPolicy) = 0;
  NS_IMETHOD SetSecurityPolicy(void * aSecurityPolicy) = 0;

  /* short canEnableCapability (in string capability); */
  NS_IMETHOD CanEnableCapability(const char *capability, PRInt16 *_retval) = 0;

  /* void setCanEnableCapability (in string capability, in short canEnable); */
  NS_IMETHOD SetCanEnableCapability(const char *capability, PRInt16 canEnable) = 0;

  /* boolean isCapabilityEnabled (in string capability, in voidPtr annotation); */
  NS_IMETHOD IsCapabilityEnabled(const char *capability, void * annotation, PRBool *_retval) = 0;

  /* void enableCapability (in string capability, inout voidPtr annotation); */
  NS_IMETHOD EnableCapability(const char *capability, void * *annotation) = 0;

  /* void revertCapability (in string capability, inout voidPtr annotation); */
  NS_IMETHOD RevertCapability(const char *capability, void * *annotation) = 0;

  /* void disableCapability (in string capability, inout voidPtr annotation); */
  NS_IMETHOD DisableCapability(const char *capability, void * *annotation) = 0;

  /**
     * The codebase URI to which this principal pertains.  This is
     * generally the document URI.
     */
  /* readonly attribute nsIURI URI; */
  NS_IMETHOD GetURI(nsIURI * *aURI) = 0;

  /**
     * The domain URI to which this principal pertains.
     * This is congruent with HTMLDocument.domain, and may be null.
     * Setting this has no effect on the URI.
     */
  /* attribute nsIURI domain; */
  NS_IMETHOD GetDomain(nsIURI * *aDomain) = 0;
  NS_IMETHOD SetDomain(nsIURI * aDomain) = 0;

  /**
     * The origin of this principal's domain, if non-null, or its
     * codebase URI otherwise. An origin is defined as:
     * scheme + host + port.
     */
  /* readonly attribute string origin; */
  NS_IMETHOD GetOrigin(char * *aOrigin) = 0;

  /**
     * Whether this principal is associated with a certificate.
     */
  /* readonly attribute boolean hasCertificate; */
  NS_IMETHOD GetHasCertificate(PRBool *aHasCertificate) = 0;

  /**
     * The fingerprint ID of this principal's certificate.
     * Throws if there is no certificate associated with this principal.
     */
  /* readonly attribute AUTF8String fingerprint; */
  NS_IMETHOD GetFingerprint(nsACString & aFingerprint) = 0;

  /**
     * The pretty name for the certificate.  This sort of (but not really)
     * identifies the subject of the certificate (the entity that stands behind
     * the certificate).  Note that this may be empty; prefer to get the
     * certificate itself and get this information from it, since that may
     * provide more information.
     *
     * Throws if there is no certificate associated with this principal.
     */
  /* readonly attribute AUTF8String prettyName; */
  NS_IMETHOD GetPrettyName(nsACString & aPrettyName) = 0;

  /**
     * Returns whether the other principal is equal to or weaker than this
     * principal.  Principals are equal if they are the same object, they
     * have the same origin, or they have the same certificate ID.
     *
     * Thus a principal subsumes itself if it is equal to itself.
     *
     * The system principal subsumes itself and all other principals except
     * the non-principal.
     *
     * The non-principal is not equal to itself or any other principal, and
     * therefore does not subsume itself.
     *
     * Both codebase and certificate principals are subsumed by the system
     * principal, but no codebase or certificate principal yet subsumes any
     * other codebase or certificate principal.  This may change in a future
     * release; note that nsIPrincipal is unfrozen, not slated to be frozen.
     */
  /* boolean subsumes (in nsIPrincipal other); */
  NS_IMETHOD Subsumes(nsIPrincipal *other, PRBool *_retval) = 0;

  /**
     * The subject name for the certificate.  This actually identifies the
     * subject of the certificate.  This may well not be a string that would
     * mean much to a typical user on its own (e.g. it may have a number of
     * different names all concatenated together with some information on what
     * they mean in between).
     *
     * Throws if there is no certificate associated with this principal.
     */
  /* readonly attribute AUTF8String subjectName; */
  NS_IMETHOD GetSubjectName(nsACString & aSubjectName) = 0;

  /**
     * The certificate associated with this principal, if any.  If there isn't
     * one, this will return null.  Getting this attribute never throws.
     */
  /* readonly attribute nsISupports certificate; */
  NS_IMETHOD GetCertificate(nsISupports * *aCertificate) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIPRINCIPAL \
  NS_IMETHOD GetPreferences(char **prefBranch, char **id, char **subjectName, char **grantedList, char **deniedList); \
  NS_IMETHOD Equals(nsIPrincipal *other, PRBool *_retval); \
  NS_IMETHOD GetHashValue(PRUint32 *aHashValue); \
  NS_IMETHOD GetJSPrincipals(JSContext * cx, JSPrincipals * *_retval); \
  NS_IMETHOD GetSecurityPolicy(void * *aSecurityPolicy); \
  NS_IMETHOD SetSecurityPolicy(void * aSecurityPolicy); \
  NS_IMETHOD CanEnableCapability(const char *capability, PRInt16 *_retval); \
  NS_IMETHOD SetCanEnableCapability(const char *capability, PRInt16 canEnable); \
  NS_IMETHOD IsCapabilityEnabled(const char *capability, void * annotation, PRBool *_retval); \
  NS_IMETHOD EnableCapability(const char *capability, void * *annotation); \
  NS_IMETHOD RevertCapability(const char *capability, void * *annotation); \
  NS_IMETHOD DisableCapability(const char *capability, void * *annotation); \
  NS_IMETHOD GetURI(nsIURI * *aURI); \
  NS_IMETHOD GetDomain(nsIURI * *aDomain); \
  NS_IMETHOD SetDomain(nsIURI * aDomain); \
  NS_IMETHOD GetOrigin(char * *aOrigin); \
  NS_IMETHOD GetHasCertificate(PRBool *aHasCertificate); \
  NS_IMETHOD GetFingerprint(nsACString & aFingerprint); \
  NS_IMETHOD GetPrettyName(nsACString & aPrettyName); \
  NS_IMETHOD Subsumes(nsIPrincipal *other, PRBool *_retval); \
  NS_IMETHOD GetSubjectName(nsACString & aSubjectName); \
  NS_IMETHOD GetCertificate(nsISupports * *aCertificate); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIPRINCIPAL(_to) \
  NS_IMETHOD GetPreferences(char **prefBranch, char **id, char **subjectName, char **grantedList, char **deniedList) { return _to GetPreferences(prefBranch, id, subjectName, grantedList, deniedList); } \
  NS_IMETHOD Equals(nsIPrincipal *other, PRBool *_retval) { return _to Equals(other, _retval); } \
  NS_IMETHOD GetHashValue(PRUint32 *aHashValue) { return _to GetHashValue(aHashValue); } \
  NS_IMETHOD GetJSPrincipals(JSContext * cx, JSPrincipals * *_retval) { return _to GetJSPrincipals(cx, _retval); } \
  NS_IMETHOD GetSecurityPolicy(void * *aSecurityPolicy) { return _to GetSecurityPolicy(aSecurityPolicy); } \
  NS_IMETHOD SetSecurityPolicy(void * aSecurityPolicy) { return _to SetSecurityPolicy(aSecurityPolicy); } \
  NS_IMETHOD CanEnableCapability(const char *capability, PRInt16 *_retval) { return _to CanEnableCapability(capability, _retval); } \
  NS_IMETHOD SetCanEnableCapability(const char *capability, PRInt16 canEnable) { return _to SetCanEnableCapability(capability, canEnable); } \
  NS_IMETHOD IsCapabilityEnabled(const char *capability, void * annotation, PRBool *_retval) { return _to IsCapabilityEnabled(capability, annotation, _retval); } \
  NS_IMETHOD EnableCapability(const char *capability, void * *annotation) { return _to EnableCapability(capability, annotation); } \
  NS_IMETHOD RevertCapability(const char *capability, void * *annotation) { return _to RevertCapability(capability, annotation); } \
  NS_IMETHOD DisableCapability(const char *capability, void * *annotation) { return _to DisableCapability(capability, annotation); } \
  NS_IMETHOD GetURI(nsIURI * *aURI) { return _to GetURI(aURI); } \
  NS_IMETHOD GetDomain(nsIURI * *aDomain) { return _to GetDomain(aDomain); } \
  NS_IMETHOD SetDomain(nsIURI * aDomain) { return _to SetDomain(aDomain); } \
  NS_IMETHOD GetOrigin(char * *aOrigin) { return _to GetOrigin(aOrigin); } \
  NS_IMETHOD GetHasCertificate(PRBool *aHasCertificate) { return _to GetHasCertificate(aHasCertificate); } \
  NS_IMETHOD GetFingerprint(nsACString & aFingerprint) { return _to GetFingerprint(aFingerprint); } \
  NS_IMETHOD GetPrettyName(nsACString & aPrettyName) { return _to GetPrettyName(aPrettyName); } \
  NS_IMETHOD Subsumes(nsIPrincipal *other, PRBool *_retval) { return _to Subsumes(other, _retval); } \
  NS_IMETHOD GetSubjectName(nsACString & aSubjectName) { return _to GetSubjectName(aSubjectName); } \
  NS_IMETHOD GetCertificate(nsISupports * *aCertificate) { return _to GetCertificate(aCertificate); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIPRINCIPAL(_to) \
  NS_IMETHOD GetPreferences(char **prefBranch, char **id, char **subjectName, char **grantedList, char **deniedList) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetPreferences(prefBranch, id, subjectName, grantedList, deniedList); } \
  NS_IMETHOD Equals(nsIPrincipal *other, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Equals(other, _retval); } \
  NS_IMETHOD GetHashValue(PRUint32 *aHashValue) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetHashValue(aHashValue); } \
  NS_IMETHOD GetJSPrincipals(JSContext * cx, JSPrincipals * *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetJSPrincipals(cx, _retval); } \
  NS_IMETHOD GetSecurityPolicy(void * *aSecurityPolicy) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetSecurityPolicy(aSecurityPolicy); } \
  NS_IMETHOD SetSecurityPolicy(void * aSecurityPolicy) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetSecurityPolicy(aSecurityPolicy); } \
  NS_IMETHOD CanEnableCapability(const char *capability, PRInt16 *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->CanEnableCapability(capability, _retval); } \
  NS_IMETHOD SetCanEnableCapability(const char *capability, PRInt16 canEnable) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetCanEnableCapability(capability, canEnable); } \
  NS_IMETHOD IsCapabilityEnabled(const char *capability, void * annotation, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->IsCapabilityEnabled(capability, annotation, _retval); } \
  NS_IMETHOD EnableCapability(const char *capability, void * *annotation) { return !_to ? NS_ERROR_NULL_POINTER : _to->EnableCapability(capability, annotation); } \
  NS_IMETHOD RevertCapability(const char *capability, void * *annotation) { return !_to ? NS_ERROR_NULL_POINTER : _to->RevertCapability(capability, annotation); } \
  NS_IMETHOD DisableCapability(const char *capability, void * *annotation) { return !_to ? NS_ERROR_NULL_POINTER : _to->DisableCapability(capability, annotation); } \
  NS_IMETHOD GetURI(nsIURI * *aURI) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetURI(aURI); } \
  NS_IMETHOD GetDomain(nsIURI * *aDomain) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetDomain(aDomain); } \
  NS_IMETHOD SetDomain(nsIURI * aDomain) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetDomain(aDomain); } \
  NS_IMETHOD GetOrigin(char * *aOrigin) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetOrigin(aOrigin); } \
  NS_IMETHOD GetHasCertificate(PRBool *aHasCertificate) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetHasCertificate(aHasCertificate); } \
  NS_IMETHOD GetFingerprint(nsACString & aFingerprint) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetFingerprint(aFingerprint); } \
  NS_IMETHOD GetPrettyName(nsACString & aPrettyName) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetPrettyName(aPrettyName); } \
  NS_IMETHOD Subsumes(nsIPrincipal *other, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Subsumes(other, _retval); } \
  NS_IMETHOD GetSubjectName(nsACString & aSubjectName) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetSubjectName(aSubjectName); } \
  NS_IMETHOD GetCertificate(nsISupports * *aCertificate) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCertificate(aCertificate); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsPrincipal : public nsIPrincipal
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIPRINCIPAL

  nsPrincipal();

private:
  ~nsPrincipal();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsPrincipal, nsIPrincipal)

nsPrincipal::nsPrincipal()
{
  /* member initializers and constructor code */
}

nsPrincipal::~nsPrincipal()
{
  /* destructor code */
}

/* void getPreferences (out string prefBranch, out string id, out string subjectName, out string grantedList, out string deniedList); */
NS_IMETHODIMP nsPrincipal::GetPreferences(char **prefBranch, char **id, char **subjectName, char **grantedList, char **deniedList)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* boolean equals (in nsIPrincipal other); */
NS_IMETHODIMP nsPrincipal::Equals(nsIPrincipal *other, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute unsigned long hashValue; */
NS_IMETHODIMP nsPrincipal::GetHashValue(PRUint32 *aHashValue)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* JSPrincipals getJSPrincipals (in JSContext cx); */
NS_IMETHODIMP nsPrincipal::GetJSPrincipals(JSContext * cx, JSPrincipals * *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute voidPtr securityPolicy; */
NS_IMETHODIMP nsPrincipal::GetSecurityPolicy(void * *aSecurityPolicy)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsPrincipal::SetSecurityPolicy(void * aSecurityPolicy)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* short canEnableCapability (in string capability); */
NS_IMETHODIMP nsPrincipal::CanEnableCapability(const char *capability, PRInt16 *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void setCanEnableCapability (in string capability, in short canEnable); */
NS_IMETHODIMP nsPrincipal::SetCanEnableCapability(const char *capability, PRInt16 canEnable)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* boolean isCapabilityEnabled (in string capability, in voidPtr annotation); */
NS_IMETHODIMP nsPrincipal::IsCapabilityEnabled(const char *capability, void * annotation, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void enableCapability (in string capability, inout voidPtr annotation); */
NS_IMETHODIMP nsPrincipal::EnableCapability(const char *capability, void * *annotation)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void revertCapability (in string capability, inout voidPtr annotation); */
NS_IMETHODIMP nsPrincipal::RevertCapability(const char *capability, void * *annotation)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void disableCapability (in string capability, inout voidPtr annotation); */
NS_IMETHODIMP nsPrincipal::DisableCapability(const char *capability, void * *annotation)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsIURI URI; */
NS_IMETHODIMP nsPrincipal::GetURI(nsIURI * *aURI)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute nsIURI domain; */
NS_IMETHODIMP nsPrincipal::GetDomain(nsIURI * *aDomain)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsPrincipal::SetDomain(nsIURI * aDomain)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute string origin; */
NS_IMETHODIMP nsPrincipal::GetOrigin(char * *aOrigin)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute boolean hasCertificate; */
NS_IMETHODIMP nsPrincipal::GetHasCertificate(PRBool *aHasCertificate)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute AUTF8String fingerprint; */
NS_IMETHODIMP nsPrincipal::GetFingerprint(nsACString & aFingerprint)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute AUTF8String prettyName; */
NS_IMETHODIMP nsPrincipal::GetPrettyName(nsACString & aPrettyName)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* boolean subsumes (in nsIPrincipal other); */
NS_IMETHODIMP nsPrincipal::Subsumes(nsIPrincipal *other, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute AUTF8String subjectName; */
NS_IMETHODIMP nsPrincipal::GetSubjectName(nsACString & aSubjectName)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsISupports certificate; */
NS_IMETHODIMP nsPrincipal::GetCertificate(nsISupports * *aCertificate)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_nsIPrincipal_h__ */
