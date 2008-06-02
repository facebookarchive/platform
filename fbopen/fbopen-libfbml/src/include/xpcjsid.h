/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM xpcjsid.idl
 */

#ifndef __gen_xpcjsid_h__
#define __gen_xpcjsid_h__


#ifndef __gen_nsISupports_h__
#include "nsISupports.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif

/* starting interface:    nsIJSID */
#define NS_IJSID_IID_STR "c86ae131-d101-11d2-9841-006008962422"

#define NS_IJSID_IID \
  {0xc86ae131, 0xd101, 0x11d2, \
    { 0x98, 0x41, 0x00, 0x60, 0x08, 0x96, 0x24, 0x22 }}

class NS_NO_VTABLE nsIJSID : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IJSID_IID)

  /* readonly attribute string name; */
  NS_IMETHOD GetName(char * *aName) = 0;

  /* readonly attribute string number; */
  NS_IMETHOD GetNumber(char * *aNumber) = 0;

  /* [noscript] readonly attribute nsIDPtr id; */
  NS_IMETHOD GetId(nsID * *aId) = 0;

  /* readonly attribute boolean valid; */
  NS_IMETHOD GetValid(PRBool *aValid) = 0;

  /* boolean equals (in nsIJSID other); */
  NS_IMETHOD Equals(nsIJSID *other, PRBool *_retval) = 0;

  /* void initialize (in string idString); */
  NS_IMETHOD Initialize(const char *idString) = 0;

  /* string toString (); */
  NS_IMETHOD ToString(char **_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIJSID \
  NS_IMETHOD GetName(char * *aName); \
  NS_IMETHOD GetNumber(char * *aNumber); \
  NS_IMETHOD GetId(nsID * *aId); \
  NS_IMETHOD GetValid(PRBool *aValid); \
  NS_IMETHOD Equals(nsIJSID *other, PRBool *_retval); \
  NS_IMETHOD Initialize(const char *idString); \
  NS_IMETHOD ToString(char **_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIJSID(_to) \
  NS_IMETHOD GetName(char * *aName) { return _to GetName(aName); } \
  NS_IMETHOD GetNumber(char * *aNumber) { return _to GetNumber(aNumber); } \
  NS_IMETHOD GetId(nsID * *aId) { return _to GetId(aId); } \
  NS_IMETHOD GetValid(PRBool *aValid) { return _to GetValid(aValid); } \
  NS_IMETHOD Equals(nsIJSID *other, PRBool *_retval) { return _to Equals(other, _retval); } \
  NS_IMETHOD Initialize(const char *idString) { return _to Initialize(idString); } \
  NS_IMETHOD ToString(char **_retval) { return _to ToString(_retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIJSID(_to) \
  NS_IMETHOD GetName(char * *aName) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetName(aName); } \
  NS_IMETHOD GetNumber(char * *aNumber) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetNumber(aNumber); } \
  NS_IMETHOD GetId(nsID * *aId) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetId(aId); } \
  NS_IMETHOD GetValid(PRBool *aValid) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetValid(aValid); } \
  NS_IMETHOD Equals(nsIJSID *other, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Equals(other, _retval); } \
  NS_IMETHOD Initialize(const char *idString) { return !_to ? NS_ERROR_NULL_POINTER : _to->Initialize(idString); } \
  NS_IMETHOD ToString(char **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->ToString(_retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsJSID : public nsIJSID
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIJSID

  nsJSID();

private:
  ~nsJSID();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsJSID, nsIJSID)

nsJSID::nsJSID()
{
  /* member initializers and constructor code */
}

nsJSID::~nsJSID()
{
  /* destructor code */
}

/* readonly attribute string name; */
NS_IMETHODIMP nsJSID::GetName(char * *aName)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute string number; */
NS_IMETHODIMP nsJSID::GetNumber(char * *aNumber)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* [noscript] readonly attribute nsIDPtr id; */
NS_IMETHODIMP nsJSID::GetId(nsID * *aId)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute boolean valid; */
NS_IMETHODIMP nsJSID::GetValid(PRBool *aValid)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* boolean equals (in nsIJSID other); */
NS_IMETHODIMP nsJSID::Equals(nsIJSID *other, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void initialize (in string idString); */
NS_IMETHODIMP nsJSID::Initialize(const char *idString)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* string toString (); */
NS_IMETHODIMP nsJSID::ToString(char **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


/* starting interface:    nsIJSIID */
#define NS_IJSIID_IID_STR "e08dcda0-d651-11d2-9843-006008962422"

#define NS_IJSIID_IID \
  {0xe08dcda0, 0xd651, 0x11d2, \
    { 0x98, 0x43, 0x00, 0x60, 0x08, 0x96, 0x24, 0x22 }}

class NS_NO_VTABLE nsIJSIID : public nsIJSID {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IJSIID_IID)

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIJSIID \
  /* no methods! */

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIJSIID(_to) \
  /* no methods! */

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIJSIID(_to) \
  /* no methods! */

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsJSIID : public nsIJSIID
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIJSIID

  nsJSIID();

private:
  ~nsJSIID();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsJSIID, nsIJSIID)

nsJSIID::nsJSIID()
{
  /* member initializers and constructor code */
}

nsJSIID::~nsJSIID()
{
  /* destructor code */
}

/* End of implementation class template. */
#endif


/* starting interface:    nsIJSCID */
#define NS_IJSCID_IID_STR "e3a24a60-d651-11d2-9843-006008962422"

#define NS_IJSCID_IID \
  {0xe3a24a60, 0xd651, 0x11d2, \
    { 0x98, 0x43, 0x00, 0x60, 0x08, 0x96, 0x24, 0x22 }}

class NS_NO_VTABLE nsIJSCID : public nsIJSID {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IJSCID_IID)

  /* nsISupports createInstance (); */
  NS_IMETHOD CreateInstance(nsISupports **_retval) = 0;

  /* nsISupports getService (); */
  NS_IMETHOD GetService(nsISupports **_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIJSCID \
  NS_IMETHOD CreateInstance(nsISupports **_retval); \
  NS_IMETHOD GetService(nsISupports **_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIJSCID(_to) \
  NS_IMETHOD CreateInstance(nsISupports **_retval) { return _to CreateInstance(_retval); } \
  NS_IMETHOD GetService(nsISupports **_retval) { return _to GetService(_retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIJSCID(_to) \
  NS_IMETHOD CreateInstance(nsISupports **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->CreateInstance(_retval); } \
  NS_IMETHOD GetService(nsISupports **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetService(_retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsJSCID : public nsIJSCID
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIJSCID

  nsJSCID();

private:
  ~nsJSCID();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsJSCID, nsIJSCID)

nsJSCID::nsJSCID()
{
  /* member initializers and constructor code */
}

nsJSCID::~nsJSCID()
{
  /* destructor code */
}

/* nsISupports createInstance (); */
NS_IMETHODIMP nsJSCID::CreateInstance(nsISupports **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsISupports getService (); */
NS_IMETHODIMP nsJSCID::GetService(nsISupports **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif

/********************************************************/
// {F24A14F0-4FA1-11d3-9894-006008962422}
#define NS_JS_ID_CID  \
{ 0xf24a14f0, 0x4fa1, 0x11d3, \
    { 0x98, 0x94, 0x0, 0x60, 0x8, 0x96, 0x24, 0x22 } }

#endif /* __gen_xpcjsid_h__ */
