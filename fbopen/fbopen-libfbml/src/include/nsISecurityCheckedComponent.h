/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM nsISecurityCheckedComponent.idl
 */

#ifndef __gen_nsISecurityCheckedComponent_h__
#define __gen_nsISecurityCheckedComponent_h__


#ifndef __gen_nsISupports_h__
#include "nsISupports.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif

/* starting interface:    nsISecurityCheckedComponent */
#define NS_ISECURITYCHECKEDCOMPONENT_IID_STR "0dad9e8c-a12d-4dcb-9a6f-7d09839356e1"

#define NS_ISECURITYCHECKEDCOMPONENT_IID \
  {0x0dad9e8c, 0xa12d, 0x4dcb, \
    { 0x9a, 0x6f, 0x7d, 0x09, 0x83, 0x93, 0x56, 0xe1 }}

/**
 * Each method of this interface should return a string representing the
 * script capability needed to perform the operation on the target component.
 *
 * Return values of 'AllAccess' or 'NoAccess' unconditionally allow or deny
 * access to the operation.
 */
class NS_NO_VTABLE nsISecurityCheckedComponent : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_ISECURITYCHECKEDCOMPONENT_IID)

  /* string canCreateWrapper (in nsIIDPtr iid); */
  NS_IMETHOD CanCreateWrapper(const nsIID * iid, char **_retval) = 0;

  /* string canCallMethod (in nsIIDPtr iid, in wstring methodName); */
  NS_IMETHOD CanCallMethod(const nsIID * iid, const PRUnichar *methodName, char **_retval) = 0;

  /* string canGetProperty (in nsIIDPtr iid, in wstring propertyName); */
  NS_IMETHOD CanGetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval) = 0;

  /* string canSetProperty (in nsIIDPtr iid, in wstring propertyName); */
  NS_IMETHOD CanSetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSISECURITYCHECKEDCOMPONENT \
  NS_IMETHOD CanCreateWrapper(const nsIID * iid, char **_retval); \
  NS_IMETHOD CanCallMethod(const nsIID * iid, const PRUnichar *methodName, char **_retval); \
  NS_IMETHOD CanGetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval); \
  NS_IMETHOD CanSetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSISECURITYCHECKEDCOMPONENT(_to) \
  NS_IMETHOD CanCreateWrapper(const nsIID * iid, char **_retval) { return _to CanCreateWrapper(iid, _retval); } \
  NS_IMETHOD CanCallMethod(const nsIID * iid, const PRUnichar *methodName, char **_retval) { return _to CanCallMethod(iid, methodName, _retval); } \
  NS_IMETHOD CanGetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval) { return _to CanGetProperty(iid, propertyName, _retval); } \
  NS_IMETHOD CanSetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval) { return _to CanSetProperty(iid, propertyName, _retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSISECURITYCHECKEDCOMPONENT(_to) \
  NS_IMETHOD CanCreateWrapper(const nsIID * iid, char **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->CanCreateWrapper(iid, _retval); } \
  NS_IMETHOD CanCallMethod(const nsIID * iid, const PRUnichar *methodName, char **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->CanCallMethod(iid, methodName, _retval); } \
  NS_IMETHOD CanGetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->CanGetProperty(iid, propertyName, _retval); } \
  NS_IMETHOD CanSetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->CanSetProperty(iid, propertyName, _retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsSecurityCheckedComponent : public nsISecurityCheckedComponent
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSISECURITYCHECKEDCOMPONENT

  nsSecurityCheckedComponent();

private:
  ~nsSecurityCheckedComponent();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsSecurityCheckedComponent, nsISecurityCheckedComponent)

nsSecurityCheckedComponent::nsSecurityCheckedComponent()
{
  /* member initializers and constructor code */
}

nsSecurityCheckedComponent::~nsSecurityCheckedComponent()
{
  /* destructor code */
}

/* string canCreateWrapper (in nsIIDPtr iid); */
NS_IMETHODIMP nsSecurityCheckedComponent::CanCreateWrapper(const nsIID * iid, char **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* string canCallMethod (in nsIIDPtr iid, in wstring methodName); */
NS_IMETHODIMP nsSecurityCheckedComponent::CanCallMethod(const nsIID * iid, const PRUnichar *methodName, char **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* string canGetProperty (in nsIIDPtr iid, in wstring propertyName); */
NS_IMETHODIMP nsSecurityCheckedComponent::CanGetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* string canSetProperty (in nsIIDPtr iid, in wstring propertyName); */
NS_IMETHODIMP nsSecurityCheckedComponent::CanSetProperty(const nsIID * iid, const PRUnichar *propertyName, char **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_nsISecurityCheckedComponent_h__ */
