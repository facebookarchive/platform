/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM nsIXPCScriptable.idl
 */

#ifndef __gen_nsIXPCScriptable_h__
#define __gen_nsIXPCScriptable_h__


#ifndef __gen_nsISupports_h__
#include "nsISupports.h"
#endif

#ifndef __gen_nsIXPConnect_h__
#include "nsIXPConnect.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif
#define NS_SUCCESS_I_DID_SOMETHING \
   (NS_ERROR_GENERATE_SUCCESS(NS_ERROR_MODULE_XPCONNECT,1))

/* starting interface:    nsIXPCScriptable */
#define NS_IXPCSCRIPTABLE_IID_STR "9cc0c2e0-f769-4f14-8cd6-2d2d40466f6c"

#define NS_IXPCSCRIPTABLE_IID \
  {0x9cc0c2e0, 0xf769, 0x4f14, \
    { 0x8c, 0xd6, 0x2d, 0x2d, 0x40, 0x46, 0x6f, 0x6c }}

/***************************************************************************/
/***************************************************************************/
class NS_NO_VTABLE nsIXPCScriptable : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCSCRIPTABLE_IID)

  /**
 * Note: This is not really an XPCOM interface.  For example, callers must
 * guarantee that they set the *_retval of the various methods that return a
 * boolean to PR_TRUE before making the call.  Implementations may skip writing
 * to *_retval unless they want to return PR_FALSE.
 */
  enum { WANT_PRECREATE = 1U };

  enum { WANT_CREATE = 2U };

  enum { WANT_POSTCREATE = 4U };

  enum { WANT_ADDPROPERTY = 8U };

  enum { WANT_DELPROPERTY = 16U };

  enum { WANT_GETPROPERTY = 32U };

  enum { WANT_SETPROPERTY = 64U };

  enum { WANT_ENUMERATE = 128U };

  enum { WANT_NEWENUMERATE = 256U };

  enum { WANT_NEWRESOLVE = 512U };

  enum { WANT_CONVERT = 1024U };

  enum { WANT_FINALIZE = 2048U };

  enum { WANT_CHECKACCESS = 4096U };

  enum { WANT_CALL = 8192U };

  enum { WANT_CONSTRUCT = 16384U };

  enum { WANT_HASINSTANCE = 32768U };

  enum { WANT_MARK = 65536U };

  enum { USE_JSSTUB_FOR_ADDPROPERTY = 131072U };

  enum { USE_JSSTUB_FOR_DELPROPERTY = 262144U };

  enum { USE_JSSTUB_FOR_SETPROPERTY = 524288U };

  enum { DONT_ENUM_STATIC_PROPS = 1048576U };

  enum { DONT_ENUM_QUERY_INTERFACE = 2097152U };

  enum { DONT_ASK_INSTANCE_FOR_SCRIPTABLE = 4194304U };

  enum { CLASSINFO_INTERFACES_ONLY = 8388608U };

  enum { ALLOW_PROP_MODS_DURING_RESOLVE = 16777216U };

  enum { ALLOW_PROP_MODS_TO_PROTOTYPE = 33554432U };

  enum { DONT_SHARE_PROTOTYPE = 67108864U };

  enum { DONT_REFLECT_INTERFACE_NAMES = 134217728U };

  enum { WANT_EQUALITY = 268435456U };

  enum { WANT_OUTER_OBJECT = 536870912U };

  enum { WANT_INNER_OBJECT = 1073741824U };

  enum { RESERVED = 2147483648U };

  /* readonly attribute string className; */
  NS_IMETHOD GetClassName(char * *aClassName) = 0;

  /* readonly attribute PRUint32 scriptableFlags; */
  NS_IMETHOD GetScriptableFlags(PRUint32 *aScriptableFlags) = 0;

  /* void preCreate (in nsISupports nativeObj, in JSContextPtr cx, in JSObjectPtr globalObj, out JSObjectPtr parentObj); */
  NS_IMETHOD PreCreate(nsISupports *nativeObj, JSContext * cx, JSObject * globalObj, JSObject * *parentObj) = 0;

  /* void create (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
  NS_IMETHOD Create(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj) = 0;

  /* void postCreate (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
  NS_IMETHOD PostCreate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj) = 0;

  /* PRBool addProperty (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in JSValPtr vp); */
  NS_IMETHOD AddProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) = 0;

  /* PRBool delProperty (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in JSValPtr vp); */
  NS_IMETHOD DelProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) = 0;

  /* PRBool getProperty (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in JSValPtr vp); */
  NS_IMETHOD GetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) = 0;

  /* PRBool setProperty (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in JSValPtr vp); */
  NS_IMETHOD SetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) = 0;

  /* PRBool enumerate (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
  NS_IMETHOD Enumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRBool *_retval) = 0;

  /* PRBool newEnumerate (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in PRUint32 enum_op, in JSValPtr statep, out JSID idp); */
  NS_IMETHOD NewEnumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 enum_op, jsval * statep, jsid *idp, PRBool *_retval) = 0;

  /* PRBool newResolve (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in PRUint32 flags, out JSObjectPtr objp); */
  NS_IMETHOD NewResolve(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 flags, JSObject * *objp, PRBool *_retval) = 0;

  /* PRBool convert (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in PRUint32 type, in JSValPtr vp); */
  NS_IMETHOD Convert(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 type, jsval * vp, PRBool *_retval) = 0;

  /* void finalize (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
  NS_IMETHOD Finalize(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj) = 0;

  /* PRBool checkAccess (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in PRUint32 mode, in JSValPtr vp); */
  NS_IMETHOD CheckAccess(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 mode, jsval * vp, PRBool *_retval) = 0;

  /* PRBool call (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in PRUint32 argc, in JSValPtr argv, in JSValPtr vp); */
  NS_IMETHOD Call(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval) = 0;

  /* PRBool construct (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in PRUint32 argc, in JSValPtr argv, in JSValPtr vp); */
  NS_IMETHOD Construct(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval) = 0;

  /* PRBool hasInstance (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal val, out PRBool bp); */
  NS_IMETHOD HasInstance(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *bp, PRBool *_retval) = 0;

  /* PRUint32 mark (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in voidPtr arg); */
  NS_IMETHOD Mark(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, void * arg, PRUint32 *_retval) = 0;

  /* PRBool equality (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal val); */
  NS_IMETHOD Equality(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *_retval) = 0;

  /* JSObjectPtr outerObject (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
  NS_IMETHOD OuterObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval) = 0;

  /* JSObjectPtr innerObject (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
  NS_IMETHOD InnerObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCSCRIPTABLE \
  NS_IMETHOD GetClassName(char * *aClassName); \
  NS_IMETHOD GetScriptableFlags(PRUint32 *aScriptableFlags); \
  NS_IMETHOD PreCreate(nsISupports *nativeObj, JSContext * cx, JSObject * globalObj, JSObject * *parentObj); \
  NS_IMETHOD Create(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj); \
  NS_IMETHOD PostCreate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj); \
  NS_IMETHOD AddProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval); \
  NS_IMETHOD DelProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval); \
  NS_IMETHOD GetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval); \
  NS_IMETHOD SetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval); \
  NS_IMETHOD Enumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRBool *_retval); \
  NS_IMETHOD NewEnumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 enum_op, jsval * statep, jsid *idp, PRBool *_retval); \
  NS_IMETHOD NewResolve(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 flags, JSObject * *objp, PRBool *_retval); \
  NS_IMETHOD Convert(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 type, jsval * vp, PRBool *_retval); \
  NS_IMETHOD Finalize(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj); \
  NS_IMETHOD CheckAccess(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 mode, jsval * vp, PRBool *_retval); \
  NS_IMETHOD Call(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval); \
  NS_IMETHOD Construct(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval); \
  NS_IMETHOD HasInstance(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *bp, PRBool *_retval); \
  NS_IMETHOD Mark(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, void * arg, PRUint32 *_retval); \
  NS_IMETHOD Equality(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *_retval); \
  NS_IMETHOD OuterObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval); \
  NS_IMETHOD InnerObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCSCRIPTABLE(_to) \
  NS_IMETHOD GetClassName(char * *aClassName) { return _to GetClassName(aClassName); } \
  NS_IMETHOD GetScriptableFlags(PRUint32 *aScriptableFlags) { return _to GetScriptableFlags(aScriptableFlags); } \
  NS_IMETHOD PreCreate(nsISupports *nativeObj, JSContext * cx, JSObject * globalObj, JSObject * *parentObj) { return _to PreCreate(nativeObj, cx, globalObj, parentObj); } \
  NS_IMETHOD Create(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj) { return _to Create(wrapper, cx, obj); } \
  NS_IMETHOD PostCreate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj) { return _to PostCreate(wrapper, cx, obj); } \
  NS_IMETHOD AddProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) { return _to AddProperty(wrapper, cx, obj, id, vp, _retval); } \
  NS_IMETHOD DelProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) { return _to DelProperty(wrapper, cx, obj, id, vp, _retval); } \
  NS_IMETHOD GetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) { return _to GetProperty(wrapper, cx, obj, id, vp, _retval); } \
  NS_IMETHOD SetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) { return _to SetProperty(wrapper, cx, obj, id, vp, _retval); } \
  NS_IMETHOD Enumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRBool *_retval) { return _to Enumerate(wrapper, cx, obj, _retval); } \
  NS_IMETHOD NewEnumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 enum_op, jsval * statep, jsid *idp, PRBool *_retval) { return _to NewEnumerate(wrapper, cx, obj, enum_op, statep, idp, _retval); } \
  NS_IMETHOD NewResolve(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 flags, JSObject * *objp, PRBool *_retval) { return _to NewResolve(wrapper, cx, obj, id, flags, objp, _retval); } \
  NS_IMETHOD Convert(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 type, jsval * vp, PRBool *_retval) { return _to Convert(wrapper, cx, obj, type, vp, _retval); } \
  NS_IMETHOD Finalize(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj) { return _to Finalize(wrapper, cx, obj); } \
  NS_IMETHOD CheckAccess(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 mode, jsval * vp, PRBool *_retval) { return _to CheckAccess(wrapper, cx, obj, id, mode, vp, _retval); } \
  NS_IMETHOD Call(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval) { return _to Call(wrapper, cx, obj, argc, argv, vp, _retval); } \
  NS_IMETHOD Construct(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval) { return _to Construct(wrapper, cx, obj, argc, argv, vp, _retval); } \
  NS_IMETHOD HasInstance(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *bp, PRBool *_retval) { return _to HasInstance(wrapper, cx, obj, val, bp, _retval); } \
  NS_IMETHOD Mark(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, void * arg, PRUint32 *_retval) { return _to Mark(wrapper, cx, obj, arg, _retval); } \
  NS_IMETHOD Equality(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *_retval) { return _to Equality(wrapper, cx, obj, val, _retval); } \
  NS_IMETHOD OuterObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval) { return _to OuterObject(wrapper, cx, obj, _retval); } \
  NS_IMETHOD InnerObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval) { return _to InnerObject(wrapper, cx, obj, _retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCSCRIPTABLE(_to) \
  NS_IMETHOD GetClassName(char * *aClassName) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetClassName(aClassName); } \
  NS_IMETHOD GetScriptableFlags(PRUint32 *aScriptableFlags) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetScriptableFlags(aScriptableFlags); } \
  NS_IMETHOD PreCreate(nsISupports *nativeObj, JSContext * cx, JSObject * globalObj, JSObject * *parentObj) { return !_to ? NS_ERROR_NULL_POINTER : _to->PreCreate(nativeObj, cx, globalObj, parentObj); } \
  NS_IMETHOD Create(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj) { return !_to ? NS_ERROR_NULL_POINTER : _to->Create(wrapper, cx, obj); } \
  NS_IMETHOD PostCreate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj) { return !_to ? NS_ERROR_NULL_POINTER : _to->PostCreate(wrapper, cx, obj); } \
  NS_IMETHOD AddProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->AddProperty(wrapper, cx, obj, id, vp, _retval); } \
  NS_IMETHOD DelProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->DelProperty(wrapper, cx, obj, id, vp, _retval); } \
  NS_IMETHOD GetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetProperty(wrapper, cx, obj, id, vp, _retval); } \
  NS_IMETHOD SetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetProperty(wrapper, cx, obj, id, vp, _retval); } \
  NS_IMETHOD Enumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Enumerate(wrapper, cx, obj, _retval); } \
  NS_IMETHOD NewEnumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 enum_op, jsval * statep, jsid *idp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->NewEnumerate(wrapper, cx, obj, enum_op, statep, idp, _retval); } \
  NS_IMETHOD NewResolve(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 flags, JSObject * *objp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->NewResolve(wrapper, cx, obj, id, flags, objp, _retval); } \
  NS_IMETHOD Convert(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 type, jsval * vp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Convert(wrapper, cx, obj, type, vp, _retval); } \
  NS_IMETHOD Finalize(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj) { return !_to ? NS_ERROR_NULL_POINTER : _to->Finalize(wrapper, cx, obj); } \
  NS_IMETHOD CheckAccess(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 mode, jsval * vp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->CheckAccess(wrapper, cx, obj, id, mode, vp, _retval); } \
  NS_IMETHOD Call(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Call(wrapper, cx, obj, argc, argv, vp, _retval); } \
  NS_IMETHOD Construct(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Construct(wrapper, cx, obj, argc, argv, vp, _retval); } \
  NS_IMETHOD HasInstance(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *bp, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->HasInstance(wrapper, cx, obj, val, bp, _retval); } \
  NS_IMETHOD Mark(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, void * arg, PRUint32 *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Mark(wrapper, cx, obj, arg, _retval); } \
  NS_IMETHOD Equality(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Equality(wrapper, cx, obj, val, _retval); } \
  NS_IMETHOD OuterObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->OuterObject(wrapper, cx, obj, _retval); } \
  NS_IMETHOD InnerObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->InnerObject(wrapper, cx, obj, _retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPCScriptable : public nsIXPCScriptable
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCSCRIPTABLE

  nsXPCScriptable();

private:
  ~nsXPCScriptable();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPCScriptable, nsIXPCScriptable)

nsXPCScriptable::nsXPCScriptable()
{
  /* member initializers and constructor code */
}

nsXPCScriptable::~nsXPCScriptable()
{
  /* destructor code */
}

/* readonly attribute string className; */
NS_IMETHODIMP nsXPCScriptable::GetClassName(char * *aClassName)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute PRUint32 scriptableFlags; */
NS_IMETHODIMP nsXPCScriptable::GetScriptableFlags(PRUint32 *aScriptableFlags)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void preCreate (in nsISupports nativeObj, in JSContextPtr cx, in JSObjectPtr globalObj, out JSObjectPtr parentObj); */
NS_IMETHODIMP nsXPCScriptable::PreCreate(nsISupports *nativeObj, JSContext * cx, JSObject * globalObj, JSObject * *parentObj)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void create (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
NS_IMETHODIMP nsXPCScriptable::Create(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void postCreate (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
NS_IMETHODIMP nsXPCScriptable::PostCreate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool addProperty (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in JSValPtr vp); */
NS_IMETHODIMP nsXPCScriptable::AddProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool delProperty (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in JSValPtr vp); */
NS_IMETHODIMP nsXPCScriptable::DelProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool getProperty (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in JSValPtr vp); */
NS_IMETHODIMP nsXPCScriptable::GetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool setProperty (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in JSValPtr vp); */
NS_IMETHODIMP nsXPCScriptable::SetProperty(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, jsval * vp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool enumerate (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
NS_IMETHODIMP nsXPCScriptable::Enumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool newEnumerate (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in PRUint32 enum_op, in JSValPtr statep, out JSID idp); */
NS_IMETHODIMP nsXPCScriptable::NewEnumerate(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 enum_op, jsval * statep, jsid *idp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool newResolve (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in PRUint32 flags, out JSObjectPtr objp); */
NS_IMETHODIMP nsXPCScriptable::NewResolve(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 flags, JSObject * *objp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool convert (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in PRUint32 type, in JSValPtr vp); */
NS_IMETHODIMP nsXPCScriptable::Convert(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 type, jsval * vp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void finalize (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
NS_IMETHODIMP nsXPCScriptable::Finalize(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool checkAccess (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal id, in PRUint32 mode, in JSValPtr vp); */
NS_IMETHODIMP nsXPCScriptable::CheckAccess(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval id, PRUint32 mode, jsval * vp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool call (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in PRUint32 argc, in JSValPtr argv, in JSValPtr vp); */
NS_IMETHODIMP nsXPCScriptable::Call(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool construct (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in PRUint32 argc, in JSValPtr argv, in JSValPtr vp); */
NS_IMETHODIMP nsXPCScriptable::Construct(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, PRUint32 argc, jsval * argv, jsval * vp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool hasInstance (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal val, out PRBool bp); */
NS_IMETHODIMP nsXPCScriptable::HasInstance(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *bp, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRUint32 mark (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in voidPtr arg); */
NS_IMETHODIMP nsXPCScriptable::Mark(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, void * arg, PRUint32 *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* PRBool equality (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj, in JSVal val); */
NS_IMETHODIMP nsXPCScriptable::Equality(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, jsval val, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* JSObjectPtr outerObject (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
NS_IMETHODIMP nsXPCScriptable::OuterObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* JSObjectPtr innerObject (in nsIXPConnectWrappedNative wrapper, in JSContextPtr cx, in JSObjectPtr obj); */
NS_IMETHODIMP nsXPCScriptable::InnerObject(nsIXPConnectWrappedNative *wrapper, JSContext * cx, JSObject * obj, JSObject * *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_nsIXPCScriptable_h__ */
