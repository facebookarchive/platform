/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM nsIXPConnect.idl
 */

#ifndef __gen_nsIXPConnect_h__
#define __gen_nsIXPConnect_h__


#ifndef __gen_nsISupports_h__
#include "nsISupports.h"
#endif

#ifndef __gen_nsIClassInfo_h__
#include "nsIClassInfo.h"
#endif

#ifndef __gen_xpccomponents_h__
#include "xpccomponents.h"
#endif

#ifndef __gen_xpcjsid_h__
#include "xpcjsid.h"
#endif

#ifndef __gen_xpcexception_h__
#include "xpcexception.h"
#endif

#ifndef __gen_nsIInterfaceInfo_h__
#include "nsIInterfaceInfo.h"
#endif

#ifndef __gen_nsIInterfaceInfoManager_h__
#include "nsIInterfaceInfoManager.h"
#endif

#ifndef __gen_nsIExceptionService_h__
#include "nsIExceptionService.h"
#endif

#ifndef __gen_nsIVariant_h__
#include "nsIVariant.h"
#endif

#ifndef __gen_nsIWeakReference_h__
#include "nsIWeakReference.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif
#include "jspubtd.h"
#include "xptinfo.h"
/***************************************************************************/
#define GENERATE_XPC_FAILURE(x) \
            (NS_ERROR_GENERATE_FAILURE(NS_ERROR_MODULE_XPCONNECT,x))
#define NS_ERROR_XPC_NOT_ENOUGH_ARGS                   GENERATE_XPC_FAILURE( 1)
#define NS_ERROR_XPC_NEED_OUT_OBJECT                   GENERATE_XPC_FAILURE( 2)
#define NS_ERROR_XPC_CANT_SET_OUT_VAL                  GENERATE_XPC_FAILURE( 3)
#define NS_ERROR_XPC_NATIVE_RETURNED_FAILURE           GENERATE_XPC_FAILURE( 4)
#define NS_ERROR_XPC_CANT_GET_INTERFACE_INFO           GENERATE_XPC_FAILURE( 5)
#define NS_ERROR_XPC_CANT_GET_PARAM_IFACE_INFO         GENERATE_XPC_FAILURE( 6)
#define NS_ERROR_XPC_CANT_GET_METHOD_INFO              GENERATE_XPC_FAILURE( 7)
#define NS_ERROR_XPC_UNEXPECTED                        GENERATE_XPC_FAILURE( 8)
#define NS_ERROR_XPC_BAD_CONVERT_JS                    GENERATE_XPC_FAILURE( 9)
#define NS_ERROR_XPC_BAD_CONVERT_NATIVE                GENERATE_XPC_FAILURE(10)
#define NS_ERROR_XPC_BAD_CONVERT_JS_NULL_REF           GENERATE_XPC_FAILURE(11)
#define NS_ERROR_XPC_BAD_OP_ON_WN_PROTO                GENERATE_XPC_FAILURE(12)
#define NS_ERROR_XPC_CANT_CONVERT_WN_TO_FUN            GENERATE_XPC_FAILURE(13)
#define NS_ERROR_XPC_CANT_DEFINE_PROP_ON_WN            GENERATE_XPC_FAILURE(14)
#define NS_ERROR_XPC_CANT_WATCH_WN_STATIC              GENERATE_XPC_FAILURE(15)
#define NS_ERROR_XPC_CANT_EXPORT_WN_STATIC             GENERATE_XPC_FAILURE(16)
#define NS_ERROR_XPC_SCRIPTABLE_CALL_FAILED            GENERATE_XPC_FAILURE(17)
#define NS_ERROR_XPC_SCRIPTABLE_CTOR_FAILED            GENERATE_XPC_FAILURE(18)
#define NS_ERROR_XPC_CANT_CALL_WO_SCRIPTABLE           GENERATE_XPC_FAILURE(19)
#define NS_ERROR_XPC_CANT_CTOR_WO_SCRIPTABLE           GENERATE_XPC_FAILURE(20)
#define NS_ERROR_XPC_CI_RETURNED_FAILURE               GENERATE_XPC_FAILURE(21)
#define NS_ERROR_XPC_GS_RETURNED_FAILURE               GENERATE_XPC_FAILURE(22)
#define NS_ERROR_XPC_BAD_CID                           GENERATE_XPC_FAILURE(23)
#define NS_ERROR_XPC_BAD_IID                           GENERATE_XPC_FAILURE(24)
#define NS_ERROR_XPC_CANT_CREATE_WN                    GENERATE_XPC_FAILURE(25)
#define NS_ERROR_XPC_JS_THREW_EXCEPTION                GENERATE_XPC_FAILURE(26)
#define NS_ERROR_XPC_JS_THREW_NATIVE_OBJECT            GENERATE_XPC_FAILURE(27)
#define NS_ERROR_XPC_JS_THREW_JS_OBJECT                GENERATE_XPC_FAILURE(28)
#define NS_ERROR_XPC_JS_THREW_NULL                     GENERATE_XPC_FAILURE(29)
#define NS_ERROR_XPC_JS_THREW_STRING                   GENERATE_XPC_FAILURE(30)
#define NS_ERROR_XPC_JS_THREW_NUMBER                   GENERATE_XPC_FAILURE(31)
#define NS_ERROR_XPC_JAVASCRIPT_ERROR                  GENERATE_XPC_FAILURE(32)
#define NS_ERROR_XPC_JAVASCRIPT_ERROR_WITH_DETAILS     GENERATE_XPC_FAILURE(33)
#define NS_ERROR_XPC_CANT_CONVERT_PRIMITIVE_TO_ARRAY   GENERATE_XPC_FAILURE(34)
#define NS_ERROR_XPC_CANT_CONVERT_OBJECT_TO_ARRAY      GENERATE_XPC_FAILURE(35)
#define NS_ERROR_XPC_NOT_ENOUGH_ELEMENTS_IN_ARRAY      GENERATE_XPC_FAILURE(36)
#define NS_ERROR_XPC_CANT_GET_ARRAY_INFO               GENERATE_XPC_FAILURE(37)
#define NS_ERROR_XPC_NOT_ENOUGH_CHARS_IN_STRING        GENERATE_XPC_FAILURE(38)
#define NS_ERROR_XPC_SECURITY_MANAGER_VETO             GENERATE_XPC_FAILURE(39)
#define NS_ERROR_XPC_INTERFACE_NOT_SCRIPTABLE          GENERATE_XPC_FAILURE(40)
#define NS_ERROR_XPC_INTERFACE_NOT_FROM_NSISUPPORTS    GENERATE_XPC_FAILURE(41)
#define NS_ERROR_XPC_CANT_GET_JSOBJECT_OF_DOM_OBJECT   GENERATE_XPC_FAILURE(42)
#define NS_ERROR_XPC_CANT_SET_READ_ONLY_CONSTANT       GENERATE_XPC_FAILURE(43)
#define NS_ERROR_XPC_CANT_SET_READ_ONLY_ATTRIBUTE      GENERATE_XPC_FAILURE(44)
#define NS_ERROR_XPC_CANT_SET_READ_ONLY_METHOD         GENERATE_XPC_FAILURE(45)
#define NS_ERROR_XPC_CANT_ADD_PROP_TO_WRAPPED_NATIVE   GENERATE_XPC_FAILURE(46)
#define NS_ERROR_XPC_CALL_TO_SCRIPTABLE_FAILED         GENERATE_XPC_FAILURE(47)
#define NS_ERROR_XPC_JSOBJECT_HAS_NO_FUNCTION_NAMED    GENERATE_XPC_FAILURE(48)
#define NS_ERROR_XPC_BAD_ID_STRING                     GENERATE_XPC_FAILURE(49)
#define NS_ERROR_XPC_BAD_INITIALIZER_NAME              GENERATE_XPC_FAILURE(50)
#define NS_ERROR_XPC_HAS_BEEN_SHUTDOWN                 GENERATE_XPC_FAILURE(51)
#define NS_ERROR_XPC_CANT_MODIFY_PROP_ON_WN            GENERATE_XPC_FAILURE(52)
#define NS_ERROR_XPC_BAD_CONVERT_JS_ZERO_ISNOT_NULL    GENERATE_XPC_FAILURE(53)
#ifdef XPC_IDISPATCH_SUPPORT
// IDispatch support related errors
#define NS_ERROR_XPC_COM_UNKNOWN                       GENERATE_XPC_FAILURE(54)
#define NS_ERROR_XPC_COM_ERROR                         GENERATE_XPC_FAILURE(55)
#define NS_ERROR_XPC_COM_INVALID_CLASS_ID              GENERATE_XPC_FAILURE(56)
#define NS_ERROR_XPC_COM_CREATE_FAILED                 GENERATE_XPC_FAILURE(57)
#define NS_ERROR_XPC_IDISPATCH_NOT_ENABLED             GENERATE_XPC_FAILURE(58)
#endif
// any new errors here should have an associated entry added in xpc.msg
/***************************************************************************/
class nsIXPCScriptable; /* forward declaration */

class nsIXPConnect; /* forward declaration */

class nsIXPConnectWrappedNative; /* forward declaration */

class nsIInterfaceInfo; /* forward declaration */

class nsIXPCSecurityManager; /* forward declaration */

class nsIPrincipal; /* forward declaration */


/* starting interface:    nsIXPConnectJSObjectHolder */
#define NS_IXPCONNECTJSOBJECTHOLDER_IID_STR "8916a320-d118-11d3-8f3a-0010a4e73d9a"

#define NS_IXPCONNECTJSOBJECTHOLDER_IID \
  {0x8916a320, 0xd118, 0x11d3, \
    { 0x8f, 0x3a, 0x00, 0x10, 0xa4, 0xe7, 0x3d, 0x9a }}

/***************************************************************************/
class NS_NO_VTABLE nsIXPConnectJSObjectHolder : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCONNECTJSOBJECTHOLDER_IID)

  /* readonly attribute JSObjectPtr JSObject; */
  NS_IMETHOD GetJSObject(JSObject * *aJSObject) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCONNECTJSOBJECTHOLDER \
  NS_IMETHOD GetJSObject(JSObject * *aJSObject); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCONNECTJSOBJECTHOLDER(_to) \
  NS_IMETHOD GetJSObject(JSObject * *aJSObject) { return _to GetJSObject(aJSObject); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCONNECTJSOBJECTHOLDER(_to) \
  NS_IMETHOD GetJSObject(JSObject * *aJSObject) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetJSObject(aJSObject); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPConnectJSObjectHolder : public nsIXPConnectJSObjectHolder
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCONNECTJSOBJECTHOLDER

  nsXPConnectJSObjectHolder();

private:
  ~nsXPConnectJSObjectHolder();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPConnectJSObjectHolder, nsIXPConnectJSObjectHolder)

nsXPConnectJSObjectHolder::nsXPConnectJSObjectHolder()
{
  /* member initializers and constructor code */
}

nsXPConnectJSObjectHolder::~nsXPConnectJSObjectHolder()
{
  /* destructor code */
}

/* readonly attribute JSObjectPtr JSObject; */
NS_IMETHODIMP nsXPConnectJSObjectHolder::GetJSObject(JSObject * *aJSObject)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


/* starting interface:    nsIXPConnectWrappedNative */
#define NS_IXPCONNECTWRAPPEDNATIVE_IID_STR "215dbe02-94a7-11d2-ba58-00805f8a5dd7"

#define NS_IXPCONNECTWRAPPEDNATIVE_IID \
  {0x215dbe02, 0x94a7, 0x11d2, \
    { 0xba, 0x58, 0x00, 0x80, 0x5f, 0x8a, 0x5d, 0xd7 }}

class nsIXPConnectWrappedNative : public nsIXPConnectJSObjectHolder {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCONNECTWRAPPEDNATIVE_IID)

  /* readonly attribute nsISupports Native; */
  NS_IMETHOD GetNative(nsISupports * *aNative) = 0;

  /* readonly attribute JSObjectPtr JSObjectPrototype; */
  NS_IMETHOD GetJSObjectPrototype(JSObject * *aJSObjectPrototype) = 0;

  /**
     * These are here as an aid to nsIXPCScriptable implementors
     */
  /* readonly attribute nsIXPConnect XPConnect; */
  NS_IMETHOD GetXPConnect(nsIXPConnect * *aXPConnect) = 0;

  /* nsIInterfaceInfo FindInterfaceWithMember (in JSVal nameID); */
  NS_IMETHOD FindInterfaceWithMember(jsval nameID, nsIInterfaceInfo **_retval) = 0;

  /* nsIInterfaceInfo FindInterfaceWithName (in JSVal nameID); */
  NS_IMETHOD FindInterfaceWithName(jsval nameID, nsIInterfaceInfo **_retval) = 0;

  /* void debugDump (in short depth); */
  NS_IMETHOD DebugDump(PRInt16 depth) = 0;

  /* void refreshPrototype (); */
  NS_IMETHOD RefreshPrototype(void) = 0;

  /* voidPtrPtr GetSecurityInfoAddress (); */
  NS_IMETHOD GetSecurityInfoAddress(void* * *_retval) = 0;

    /**
     * Faster access to the native object from C++.  Will never return null.
     */
    nsISupports* Native() const { return mIdentity; }
protected:
    nsISupports *mIdentity;
public:
};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCONNECTWRAPPEDNATIVE \
  NS_IMETHOD GetNative(nsISupports * *aNative); \
  NS_IMETHOD GetJSObjectPrototype(JSObject * *aJSObjectPrototype); \
  NS_IMETHOD GetXPConnect(nsIXPConnect * *aXPConnect); \
  NS_IMETHOD FindInterfaceWithMember(jsval nameID, nsIInterfaceInfo **_retval); \
  NS_IMETHOD FindInterfaceWithName(jsval nameID, nsIInterfaceInfo **_retval); \
  NS_IMETHOD DebugDump(PRInt16 depth); \
  NS_IMETHOD RefreshPrototype(void); \
  NS_IMETHOD GetSecurityInfoAddress(void* * *_retval); \

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCONNECTWRAPPEDNATIVE(_to) \
  NS_IMETHOD GetNative(nsISupports * *aNative) { return _to GetNative(aNative); } \
  NS_IMETHOD GetJSObjectPrototype(JSObject * *aJSObjectPrototype) { return _to GetJSObjectPrototype(aJSObjectPrototype); } \
  NS_IMETHOD GetXPConnect(nsIXPConnect * *aXPConnect) { return _to GetXPConnect(aXPConnect); } \
  NS_IMETHOD FindInterfaceWithMember(jsval nameID, nsIInterfaceInfo **_retval) { return _to FindInterfaceWithMember(nameID, _retval); } \
  NS_IMETHOD FindInterfaceWithName(jsval nameID, nsIInterfaceInfo **_retval) { return _to FindInterfaceWithName(nameID, _retval); } \
  NS_IMETHOD DebugDump(PRInt16 depth) { return _to DebugDump(depth); } \
  NS_IMETHOD RefreshPrototype(void) { return _to RefreshPrototype(); } \
  NS_IMETHOD GetSecurityInfoAddress(void* * *_retval) { return _to GetSecurityInfoAddress(_retval); } \

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCONNECTWRAPPEDNATIVE(_to) \
  NS_IMETHOD GetNative(nsISupports * *aNative) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetNative(aNative); } \
  NS_IMETHOD GetJSObjectPrototype(JSObject * *aJSObjectPrototype) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetJSObjectPrototype(aJSObjectPrototype); } \
  NS_IMETHOD GetXPConnect(nsIXPConnect * *aXPConnect) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetXPConnect(aXPConnect); } \
  NS_IMETHOD FindInterfaceWithMember(jsval nameID, nsIInterfaceInfo **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->FindInterfaceWithMember(nameID, _retval); } \
  NS_IMETHOD FindInterfaceWithName(jsval nameID, nsIInterfaceInfo **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->FindInterfaceWithName(nameID, _retval); } \
  NS_IMETHOD DebugDump(PRInt16 depth) { return !_to ? NS_ERROR_NULL_POINTER : _to->DebugDump(depth); } \
  NS_IMETHOD RefreshPrototype(void) { return !_to ? NS_ERROR_NULL_POINTER : _to->RefreshPrototype(); } \
  NS_IMETHOD GetSecurityInfoAddress(void* * *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetSecurityInfoAddress(_retval); } \

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPConnectWrappedNative : public nsIXPConnectWrappedNative
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCONNECTWRAPPEDNATIVE

  nsXPConnectWrappedNative();

private:
  ~nsXPConnectWrappedNative();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPConnectWrappedNative, nsIXPConnectWrappedNative)

nsXPConnectWrappedNative::nsXPConnectWrappedNative()
{
  /* member initializers and constructor code */
}

nsXPConnectWrappedNative::~nsXPConnectWrappedNative()
{
  /* destructor code */
}

/* readonly attribute nsISupports Native; */
NS_IMETHODIMP nsXPConnectWrappedNative::GetNative(nsISupports * *aNative)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute JSObjectPtr JSObjectPrototype; */
NS_IMETHODIMP nsXPConnectWrappedNative::GetJSObjectPrototype(JSObject * *aJSObjectPrototype)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsIXPConnect XPConnect; */
NS_IMETHODIMP nsXPConnectWrappedNative::GetXPConnect(nsIXPConnect * *aXPConnect)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIInterfaceInfo FindInterfaceWithMember (in JSVal nameID); */
NS_IMETHODIMP nsXPConnectWrappedNative::FindInterfaceWithMember(jsval nameID, nsIInterfaceInfo **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIInterfaceInfo FindInterfaceWithName (in JSVal nameID); */
NS_IMETHODIMP nsXPConnectWrappedNative::FindInterfaceWithName(jsval nameID, nsIInterfaceInfo **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void debugDump (in short depth); */
NS_IMETHODIMP nsXPConnectWrappedNative::DebugDump(PRInt16 depth)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void refreshPrototype (); */
NS_IMETHODIMP nsXPConnectWrappedNative::RefreshPrototype()
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* voidPtrPtr GetSecurityInfoAddress (); */
NS_IMETHODIMP nsXPConnectWrappedNative::GetSecurityInfoAddress(void* * *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif

inline
const nsQueryInterface
do_QueryWrappedNative(nsIXPConnectWrappedNative *aWrappedNative)
{
    return nsQueryInterface(aWrappedNative->Native());
}
inline
const nsQueryInterfaceWithError
do_QueryWrappedNative(nsIXPConnectWrappedNative *aWrappedNative,
                      nsresult *aError)
{
    return nsQueryInterfaceWithError(aWrappedNative->Native(), aError);
}

/* starting interface:    nsIXPConnectWrappedJS */
#define NS_IXPCONNECTWRAPPEDJS_IID_STR "bed52030-bca6-11d2-ba79-00805f8a5dd7"

#define NS_IXPCONNECTWRAPPEDJS_IID \
  {0xbed52030, 0xbca6, 0x11d2, \
    { 0xba, 0x79, 0x00, 0x80, 0x5f, 0x8a, 0x5d, 0xd7 }}

class NS_NO_VTABLE nsIXPConnectWrappedJS : public nsIXPConnectJSObjectHolder {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCONNECTWRAPPEDJS_IID)

  /* readonly attribute nsIInterfaceInfo InterfaceInfo; */
  NS_IMETHOD GetInterfaceInfo(nsIInterfaceInfo * *aInterfaceInfo) = 0;

  /* readonly attribute nsIIDPtr InterfaceIID; */
  NS_IMETHOD GetInterfaceIID(nsIID * *aInterfaceIID) = 0;

  /* void debugDump (in short depth); */
  NS_IMETHOD DebugDump(PRInt16 depth) = 0;

  /* void aggregatedQueryInterface (in nsIIDRef uuid, [iid_is (uuid), retval] out nsQIResult result); */
  NS_IMETHOD AggregatedQueryInterface(const nsIID & uuid, void * *result) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCONNECTWRAPPEDJS \
  NS_IMETHOD GetInterfaceInfo(nsIInterfaceInfo * *aInterfaceInfo); \
  NS_IMETHOD GetInterfaceIID(nsIID * *aInterfaceIID); \
  NS_IMETHOD DebugDump(PRInt16 depth); \
  NS_IMETHOD AggregatedQueryInterface(const nsIID & uuid, void * *result); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCONNECTWRAPPEDJS(_to) \
  NS_IMETHOD GetInterfaceInfo(nsIInterfaceInfo * *aInterfaceInfo) { return _to GetInterfaceInfo(aInterfaceInfo); } \
  NS_IMETHOD GetInterfaceIID(nsIID * *aInterfaceIID) { return _to GetInterfaceIID(aInterfaceIID); } \
  NS_IMETHOD DebugDump(PRInt16 depth) { return _to DebugDump(depth); } \
  NS_IMETHOD AggregatedQueryInterface(const nsIID & uuid, void * *result) { return _to AggregatedQueryInterface(uuid, result); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCONNECTWRAPPEDJS(_to) \
  NS_IMETHOD GetInterfaceInfo(nsIInterfaceInfo * *aInterfaceInfo) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetInterfaceInfo(aInterfaceInfo); } \
  NS_IMETHOD GetInterfaceIID(nsIID * *aInterfaceIID) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetInterfaceIID(aInterfaceIID); } \
  NS_IMETHOD DebugDump(PRInt16 depth) { return !_to ? NS_ERROR_NULL_POINTER : _to->DebugDump(depth); } \
  NS_IMETHOD AggregatedQueryInterface(const nsIID & uuid, void * *result) { return !_to ? NS_ERROR_NULL_POINTER : _to->AggregatedQueryInterface(uuid, result); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPConnectWrappedJS : public nsIXPConnectWrappedJS
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCONNECTWRAPPEDJS

  nsXPConnectWrappedJS();

private:
  ~nsXPConnectWrappedJS();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPConnectWrappedJS, nsIXPConnectWrappedJS)

nsXPConnectWrappedJS::nsXPConnectWrappedJS()
{
  /* member initializers and constructor code */
}

nsXPConnectWrappedJS::~nsXPConnectWrappedJS()
{
  /* destructor code */
}

/* readonly attribute nsIInterfaceInfo InterfaceInfo; */
NS_IMETHODIMP nsXPConnectWrappedJS::GetInterfaceInfo(nsIInterfaceInfo * *aInterfaceInfo)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsIIDPtr InterfaceIID; */
NS_IMETHODIMP nsXPConnectWrappedJS::GetInterfaceIID(nsIID * *aInterfaceIID)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void debugDump (in short depth); */
NS_IMETHODIMP nsXPConnectWrappedJS::DebugDump(PRInt16 depth)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void aggregatedQueryInterface (in nsIIDRef uuid, [iid_is (uuid), retval] out nsQIResult result); */
NS_IMETHODIMP nsXPConnectWrappedJS::AggregatedQueryInterface(const nsIID & uuid, void * *result)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


/* starting interface:    nsIXPConnectWrappedJS_MOZILLA_1_8_BRANCH */
#define NS_IXPCONNECTWRAPPEDJS_MOZILLA_1_8_BRANCH_IID_STR "0f1799d3-13d3-45f7-8361-0a6f211183f4"

#define NS_IXPCONNECTWRAPPEDJS_MOZILLA_1_8_BRANCH_IID \
  {0x0f1799d3, 0x13d3, 0x45f7, \
    { 0x83, 0x61, 0x0a, 0x6f, 0x21, 0x11, 0x83, 0xf4 }}

class NS_NO_VTABLE nsIXPConnectWrappedJS_MOZILLA_1_8_BRANCH : public nsIXPConnectWrappedJS {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCONNECTWRAPPEDJS_MOZILLA_1_8_BRANCH_IID)

  /* nsIWeakReference GetWeakReference (); */
  NS_IMETHOD GetWeakReference(nsIWeakReference **_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCONNECTWRAPPEDJS_MOZILLA_1_8_BRANCH \
  NS_IMETHOD GetWeakReference(nsIWeakReference **_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCONNECTWRAPPEDJS_MOZILLA_1_8_BRANCH(_to) \
  NS_IMETHOD GetWeakReference(nsIWeakReference **_retval) { return _to GetWeakReference(_retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCONNECTWRAPPEDJS_MOZILLA_1_8_BRANCH(_to) \
  NS_IMETHOD GetWeakReference(nsIWeakReference **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetWeakReference(_retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPConnectWrappedJS_MOZILLA_1_8_BRANCH : public nsIXPConnectWrappedJS_MOZILLA_1_8_BRANCH
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCONNECTWRAPPEDJS_MOZILLA_1_8_BRANCH

  nsXPConnectWrappedJS_MOZILLA_1_8_BRANCH();

private:
  ~nsXPConnectWrappedJS_MOZILLA_1_8_BRANCH();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPConnectWrappedJS_MOZILLA_1_8_BRANCH, nsIXPConnectWrappedJS_MOZILLA_1_8_BRANCH)

nsXPConnectWrappedJS_MOZILLA_1_8_BRANCH::nsXPConnectWrappedJS_MOZILLA_1_8_BRANCH()
{
  /* member initializers and constructor code */
}

nsXPConnectWrappedJS_MOZILLA_1_8_BRANCH::~nsXPConnectWrappedJS_MOZILLA_1_8_BRANCH()
{
  /* destructor code */
}

/* nsIWeakReference GetWeakReference (); */
NS_IMETHODIMP nsXPConnectWrappedJS_MOZILLA_1_8_BRANCH::GetWeakReference(nsIWeakReference **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


/* starting interface:    nsWeakRefToIXPConnectWrappedJS */
#define NS_WEAKREFTOIXPCONNECTWRAPPEDJS_IID_STR "3f32871c-d014-4f91-b358-3ece74cbebaa"

#define NS_WEAKREFTOIXPCONNECTWRAPPEDJS_IID \
  {0x3f32871c, 0xd014, 0x4f91, \
    { 0xb3, 0x58, 0x3e, 0xce, 0x74, 0xcb, 0xeb, 0xaa }}

/**
 * This interface is a complete hack.  It is used by the DOM code to
 * call QueryReferent on a weak reference to a wrapped JS object without
 * causing reference counting, which would add and remove GC roots
 * (which can't be done in the middle of GC).
 */
class NS_NO_VTABLE nsWeakRefToIXPConnectWrappedJS : public nsIXPConnectWrappedJS_MOZILLA_1_8_BRANCH {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_WEAKREFTOIXPCONNECTWRAPPEDJS_IID)

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSWEAKREFTOIXPCONNECTWRAPPEDJS \
  /* no methods! */

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSWEAKREFTOIXPCONNECTWRAPPEDJS(_to) \
  /* no methods! */

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSWEAKREFTOIXPCONNECTWRAPPEDJS(_to) \
  /* no methods! */

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class _MYCLASS_ : public nsWeakRefToIXPConnectWrappedJS
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSWEAKREFTOIXPCONNECTWRAPPEDJS

  _MYCLASS_();

private:
  ~_MYCLASS_();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(_MYCLASS_, nsWeakRefToIXPConnectWrappedJS)

_MYCLASS_::_MYCLASS_()
{
  /* member initializers and constructor code */
}

_MYCLASS_::~_MYCLASS_()
{
  /* destructor code */
}

/* End of implementation class template. */
#endif


/* starting interface:    nsIXPCNativeCallContext */
#define NS_IXPCNATIVECALLCONTEXT_IID_STR "0fa68a60-8289-11d3-bb1a-00805f8a5dd7"

#define NS_IXPCNATIVECALLCONTEXT_IID \
  {0x0fa68a60, 0x8289, 0x11d3, \
    { 0xbb, 0x1a, 0x00, 0x80, 0x5f, 0x8a, 0x5d, 0xd7 }}

/***************************************************************************/
/**
* This is a somewhat special interface. It is available from the global
* nsIXPConnect object when native methods have been called. It is only relevant
* to the currently called native method on the given JSContext/thread. Holding
* a reference past that time (or while other native methods are being called)
* will not assure access to this data.
*/
class NS_NO_VTABLE nsIXPCNativeCallContext : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCNATIVECALLCONTEXT_IID)

  /* readonly attribute nsISupports Callee; */
  NS_IMETHOD GetCallee(nsISupports * *aCallee) = 0;

  /* readonly attribute PRUint16 CalleeMethodIndex; */
  NS_IMETHOD GetCalleeMethodIndex(PRUint16 *aCalleeMethodIndex) = 0;

  /* readonly attribute nsIXPConnectWrappedNative CalleeWrapper; */
  NS_IMETHOD GetCalleeWrapper(nsIXPConnectWrappedNative * *aCalleeWrapper) = 0;

  /* readonly attribute JSContextPtr JSContext; */
  NS_IMETHOD GetJSContext(JSContext * *aJSContext) = 0;

  /* readonly attribute PRUint32 Argc; */
  NS_IMETHOD GetArgc(PRUint32 *aArgc) = 0;

  /* readonly attribute JSValPtr ArgvPtr; */
  NS_IMETHOD GetArgvPtr(jsval * *aArgvPtr) = 0;

  /**
    * This may be NULL if the JS caller is ignoring the result of the call.
    */
  /* readonly attribute JSValPtr RetValPtr; */
  NS_IMETHOD GetRetValPtr(jsval * *aRetValPtr) = 0;

  /**
    * Set this if JS_SetPendingException has been called. Return NS_OK or
    * else this will be ignored and the native method's nsresult will be
    * converted into an exception and thrown into JS as is the normal case.
    */
  /* attribute PRBool ExceptionWasThrown; */
  NS_IMETHOD GetExceptionWasThrown(PRBool *aExceptionWasThrown) = 0;
  NS_IMETHOD SetExceptionWasThrown(PRBool aExceptionWasThrown) = 0;

  /**
    * Set this to indicate that the callee has directly set the return value
    * (using RetValPtr and the JSAPI). If set then xpconnect will not attempt
    * to overwrite it with the converted retval from the C++ callee.
    */
  /* attribute PRBool ReturnValueWasSet; */
  NS_IMETHOD GetReturnValueWasSet(PRBool *aReturnValueWasSet) = 0;
  NS_IMETHOD SetReturnValueWasSet(PRBool aReturnValueWasSet) = 0;

  /* readonly attribute nsIInterfaceInfo CalleeInterface; */
  NS_IMETHOD GetCalleeInterface(nsIInterfaceInfo * *aCalleeInterface) = 0;

  /* readonly attribute nsIClassInfo CalleeClassInfo; */
  NS_IMETHOD GetCalleeClassInfo(nsIClassInfo * *aCalleeClassInfo) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCNATIVECALLCONTEXT \
  NS_IMETHOD GetCallee(nsISupports * *aCallee); \
  NS_IMETHOD GetCalleeMethodIndex(PRUint16 *aCalleeMethodIndex); \
  NS_IMETHOD GetCalleeWrapper(nsIXPConnectWrappedNative * *aCalleeWrapper); \
  NS_IMETHOD GetJSContext(JSContext * *aJSContext); \
  NS_IMETHOD GetArgc(PRUint32 *aArgc); \
  NS_IMETHOD GetArgvPtr(jsval * *aArgvPtr); \
  NS_IMETHOD GetRetValPtr(jsval * *aRetValPtr); \
  NS_IMETHOD GetExceptionWasThrown(PRBool *aExceptionWasThrown); \
  NS_IMETHOD SetExceptionWasThrown(PRBool aExceptionWasThrown); \
  NS_IMETHOD GetReturnValueWasSet(PRBool *aReturnValueWasSet); \
  NS_IMETHOD SetReturnValueWasSet(PRBool aReturnValueWasSet); \
  NS_IMETHOD GetCalleeInterface(nsIInterfaceInfo * *aCalleeInterface); \
  NS_IMETHOD GetCalleeClassInfo(nsIClassInfo * *aCalleeClassInfo); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCNATIVECALLCONTEXT(_to) \
  NS_IMETHOD GetCallee(nsISupports * *aCallee) { return _to GetCallee(aCallee); } \
  NS_IMETHOD GetCalleeMethodIndex(PRUint16 *aCalleeMethodIndex) { return _to GetCalleeMethodIndex(aCalleeMethodIndex); } \
  NS_IMETHOD GetCalleeWrapper(nsIXPConnectWrappedNative * *aCalleeWrapper) { return _to GetCalleeWrapper(aCalleeWrapper); } \
  NS_IMETHOD GetJSContext(JSContext * *aJSContext) { return _to GetJSContext(aJSContext); } \
  NS_IMETHOD GetArgc(PRUint32 *aArgc) { return _to GetArgc(aArgc); } \
  NS_IMETHOD GetArgvPtr(jsval * *aArgvPtr) { return _to GetArgvPtr(aArgvPtr); } \
  NS_IMETHOD GetRetValPtr(jsval * *aRetValPtr) { return _to GetRetValPtr(aRetValPtr); } \
  NS_IMETHOD GetExceptionWasThrown(PRBool *aExceptionWasThrown) { return _to GetExceptionWasThrown(aExceptionWasThrown); } \
  NS_IMETHOD SetExceptionWasThrown(PRBool aExceptionWasThrown) { return _to SetExceptionWasThrown(aExceptionWasThrown); } \
  NS_IMETHOD GetReturnValueWasSet(PRBool *aReturnValueWasSet) { return _to GetReturnValueWasSet(aReturnValueWasSet); } \
  NS_IMETHOD SetReturnValueWasSet(PRBool aReturnValueWasSet) { return _to SetReturnValueWasSet(aReturnValueWasSet); } \
  NS_IMETHOD GetCalleeInterface(nsIInterfaceInfo * *aCalleeInterface) { return _to GetCalleeInterface(aCalleeInterface); } \
  NS_IMETHOD GetCalleeClassInfo(nsIClassInfo * *aCalleeClassInfo) { return _to GetCalleeClassInfo(aCalleeClassInfo); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCNATIVECALLCONTEXT(_to) \
  NS_IMETHOD GetCallee(nsISupports * *aCallee) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCallee(aCallee); } \
  NS_IMETHOD GetCalleeMethodIndex(PRUint16 *aCalleeMethodIndex) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCalleeMethodIndex(aCalleeMethodIndex); } \
  NS_IMETHOD GetCalleeWrapper(nsIXPConnectWrappedNative * *aCalleeWrapper) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCalleeWrapper(aCalleeWrapper); } \
  NS_IMETHOD GetJSContext(JSContext * *aJSContext) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetJSContext(aJSContext); } \
  NS_IMETHOD GetArgc(PRUint32 *aArgc) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetArgc(aArgc); } \
  NS_IMETHOD GetArgvPtr(jsval * *aArgvPtr) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetArgvPtr(aArgvPtr); } \
  NS_IMETHOD GetRetValPtr(jsval * *aRetValPtr) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetRetValPtr(aRetValPtr); } \
  NS_IMETHOD GetExceptionWasThrown(PRBool *aExceptionWasThrown) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetExceptionWasThrown(aExceptionWasThrown); } \
  NS_IMETHOD SetExceptionWasThrown(PRBool aExceptionWasThrown) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetExceptionWasThrown(aExceptionWasThrown); } \
  NS_IMETHOD GetReturnValueWasSet(PRBool *aReturnValueWasSet) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetReturnValueWasSet(aReturnValueWasSet); } \
  NS_IMETHOD SetReturnValueWasSet(PRBool aReturnValueWasSet) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetReturnValueWasSet(aReturnValueWasSet); } \
  NS_IMETHOD GetCalleeInterface(nsIInterfaceInfo * *aCalleeInterface) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCalleeInterface(aCalleeInterface); } \
  NS_IMETHOD GetCalleeClassInfo(nsIClassInfo * *aCalleeClassInfo) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCalleeClassInfo(aCalleeClassInfo); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPCNativeCallContext : public nsIXPCNativeCallContext
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCNATIVECALLCONTEXT

  nsXPCNativeCallContext();

private:
  ~nsXPCNativeCallContext();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPCNativeCallContext, nsIXPCNativeCallContext)

nsXPCNativeCallContext::nsXPCNativeCallContext()
{
  /* member initializers and constructor code */
}

nsXPCNativeCallContext::~nsXPCNativeCallContext()
{
  /* destructor code */
}

/* readonly attribute nsISupports Callee; */
NS_IMETHODIMP nsXPCNativeCallContext::GetCallee(nsISupports * *aCallee)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute PRUint16 CalleeMethodIndex; */
NS_IMETHODIMP nsXPCNativeCallContext::GetCalleeMethodIndex(PRUint16 *aCalleeMethodIndex)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsIXPConnectWrappedNative CalleeWrapper; */
NS_IMETHODIMP nsXPCNativeCallContext::GetCalleeWrapper(nsIXPConnectWrappedNative * *aCalleeWrapper)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute JSContextPtr JSContext; */
NS_IMETHODIMP nsXPCNativeCallContext::GetJSContext(JSContext * *aJSContext)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute PRUint32 Argc; */
NS_IMETHODIMP nsXPCNativeCallContext::GetArgc(PRUint32 *aArgc)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute JSValPtr ArgvPtr; */
NS_IMETHODIMP nsXPCNativeCallContext::GetArgvPtr(jsval * *aArgvPtr)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute JSValPtr RetValPtr; */
NS_IMETHODIMP nsXPCNativeCallContext::GetRetValPtr(jsval * *aRetValPtr)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute PRBool ExceptionWasThrown; */
NS_IMETHODIMP nsXPCNativeCallContext::GetExceptionWasThrown(PRBool *aExceptionWasThrown)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsXPCNativeCallContext::SetExceptionWasThrown(PRBool aExceptionWasThrown)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute PRBool ReturnValueWasSet; */
NS_IMETHODIMP nsXPCNativeCallContext::GetReturnValueWasSet(PRBool *aReturnValueWasSet)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsXPCNativeCallContext::SetReturnValueWasSet(PRBool aReturnValueWasSet)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsIInterfaceInfo CalleeInterface; */
NS_IMETHODIMP nsXPCNativeCallContext::GetCalleeInterface(nsIInterfaceInfo * *aCalleeInterface)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsIClassInfo CalleeClassInfo; */
NS_IMETHODIMP nsXPCNativeCallContext::GetCalleeClassInfo(nsIClassInfo * *aCalleeClassInfo)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


/* starting interface:    nsIXPCWrappedJSObjectGetter */
#define NS_IXPCWRAPPEDJSOBJECTGETTER_IID_STR "254bb2e0-6439-11d4-8fe0-0010a4e73d9a"

#define NS_IXPCWRAPPEDJSOBJECTGETTER_IID \
  {0x254bb2e0, 0x6439, 0x11d4, \
    { 0x8f, 0xe0, 0x00, 0x10, 0xa4, 0xe7, 0x3d, 0x9a }}

/***************************************************************************/
/**
 * This is a sort of a placeholder interface. It is not intended to be
 * implemented. It exists to give the nsIXPCSecurityManager an iid on
 * which to gate a specific activity in XPConnect.
 *
 * That activity is...
 *
 * When JavaScript code uses a component that is itself implemeted in
 * JavaScript then XPConnect will build a wrapper rather than directly
 * expose the JSObject of the component. This allows components implemented
 * in JavaScript to 'look' just like any other xpcom component (from the
 * perspective of the JavaScript caller). This insulates the component from
 * the caller and hides any properties or methods that are not part of the
 * interface as declared in xpidl. Usually this is a good thing.
 *
 * However, in some cases it is useful to allow the JS caller access to the
 * JS component's underlying implementation. In order to facilitate this
 * XPConnect supports the 'wrappedJSObject' property. The caller code can do:
 *
 * // 'foo' is some xpcom component (that might be implemented in JS).
 * try {
 *   var bar = foo.wrappedJSObject;
 *   if(bar) {
 *      // bar is the underlying JSObject. Do stuff with it here.
 *   }
 * } catch(e) {
 *   // security exception?
 * }
 *
 * Recall that 'foo' above is an XPConnect wrapper, not the underlying JS
 * object. The property get "foo.wrappedJSObject" will only succeed if three
 * conditions are met:
 *
 * 1) 'foo' really is an XPConnect wrapper around a JSObject.
 * 2) The underlying JSObject actually implements a "wrappedJSObject"
 *    property that returns a JSObject. This is called by XPConnect. This
 *    restriction allows wrapped objects to only allow access to the underlying
 *    JSObject if they choose to do so. Ususally this just means that 'foo'
 *    would have a property tht looks like:
 *       this.wrappedJSObject = this.
 * 3) The implemementation of nsIXPCSecurityManager (if installed) allows
 *    a property get on the interface below. Although the JSObject need not
 *    implement 'nsIXPCWrappedJSObjectGetter', XPConnect will ask the
 *    security manager if it is OK for the caller to access the only method
 *    in nsIXPCWrappedJSObjectGetter before allowing the activity. This fits
 *    in with the security manager paradigm and makes control over accessing
 *    the property on this interface the control factor for getting the
 *    underlying wrapped JSObject of a JS component from JS code.
 *
 * Notes:
 *
 * a) If 'foo' above were the underlying JSObject and not a wrapper at all,
 *    then this all just works and XPConnect is not part of the picture at all.
 * b) One might ask why 'foo' should not just implement an interface through
 *    which callers might get at the underlying object. There are three reasons:
 *   i)   XPConnect would still have to do magic since JSObject is not a
 *        scriptable type.
 *   ii)  JS Components might use aggregation (like C++ objects) and have
 *        different JSObjects for different interfaces 'within' an aggregate
 *        object. But, using an additional interface only allows returning one
 *        underlying JSObject. However, this allows for the possibility that
 *        each of the aggregte JSObjects could return something different.
 *        Note that one might do: this.wrappedJSObject = someOtherObject;
 *   iii) Avoiding the explicit interface makes it easier for both the caller
 *        and the component.
 *
 *  Anyway, some future implementation of nsIXPCSecurityManager might want
 *  do special processing on 'nsIXPCSecurityManager::CanGetProperty' when
 *  the interface id is that of nsIXPCWrappedJSObjectGetter.
 */
class NS_NO_VTABLE nsIXPCWrappedJSObjectGetter : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCWRAPPEDJSOBJECTGETTER_IID)

  /* readonly attribute nsISupports neverCalled; */
  NS_IMETHOD GetNeverCalled(nsISupports * *aNeverCalled) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCWRAPPEDJSOBJECTGETTER \
  NS_IMETHOD GetNeverCalled(nsISupports * *aNeverCalled); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCWRAPPEDJSOBJECTGETTER(_to) \
  NS_IMETHOD GetNeverCalled(nsISupports * *aNeverCalled) { return _to GetNeverCalled(aNeverCalled); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCWRAPPEDJSOBJECTGETTER(_to) \
  NS_IMETHOD GetNeverCalled(nsISupports * *aNeverCalled) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetNeverCalled(aNeverCalled); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPCWrappedJSObjectGetter : public nsIXPCWrappedJSObjectGetter
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCWRAPPEDJSOBJECTGETTER

  nsXPCWrappedJSObjectGetter();

private:
  ~nsXPCWrappedJSObjectGetter();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPCWrappedJSObjectGetter, nsIXPCWrappedJSObjectGetter)

nsXPCWrappedJSObjectGetter::nsXPCWrappedJSObjectGetter()
{
  /* member initializers and constructor code */
}

nsXPCWrappedJSObjectGetter::~nsXPCWrappedJSObjectGetter()
{
  /* destructor code */
}

/* readonly attribute nsISupports neverCalled; */
NS_IMETHODIMP nsXPCWrappedJSObjectGetter::GetNeverCalled(nsISupports * *aNeverCalled)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


/* starting interface:    nsIXPCFunctionThisTranslator */
#define NS_IXPCFUNCTIONTHISTRANSLATOR_IID_STR "039ef260-2a0d-11d5-90a7-0010a4e73d9a"

#define NS_IXPCFUNCTIONTHISTRANSLATOR_IID \
  {0x039ef260, 0x2a0d, 0x11d5, \
    { 0x90, 0xa7, 0x00, 0x10, 0xa4, 0xe7, 0x3d, 0x9a }}

/***************************************************************************/
class NS_NO_VTABLE nsIXPCFunctionThisTranslator : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCFUNCTIONTHISTRANSLATOR_IID)

  /* nsISupports TranslateThis (in nsISupports aInitialThis, in nsIInterfaceInfo aInterfaceInfo, in PRUint16 aMethodIndex, out PRBool aHideFirstParamFromJS, out nsIIDPtr aIIDOfResult); */
  NS_IMETHOD TranslateThis(nsISupports *aInitialThis, nsIInterfaceInfo *aInterfaceInfo, PRUint16 aMethodIndex, PRBool *aHideFirstParamFromJS, nsIID * *aIIDOfResult, nsISupports **_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCFUNCTIONTHISTRANSLATOR \
  NS_IMETHOD TranslateThis(nsISupports *aInitialThis, nsIInterfaceInfo *aInterfaceInfo, PRUint16 aMethodIndex, PRBool *aHideFirstParamFromJS, nsIID * *aIIDOfResult, nsISupports **_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCFUNCTIONTHISTRANSLATOR(_to) \
  NS_IMETHOD TranslateThis(nsISupports *aInitialThis, nsIInterfaceInfo *aInterfaceInfo, PRUint16 aMethodIndex, PRBool *aHideFirstParamFromJS, nsIID * *aIIDOfResult, nsISupports **_retval) { return _to TranslateThis(aInitialThis, aInterfaceInfo, aMethodIndex, aHideFirstParamFromJS, aIIDOfResult, _retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCFUNCTIONTHISTRANSLATOR(_to) \
  NS_IMETHOD TranslateThis(nsISupports *aInitialThis, nsIInterfaceInfo *aInterfaceInfo, PRUint16 aMethodIndex, PRBool *aHideFirstParamFromJS, nsIID * *aIIDOfResult, nsISupports **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->TranslateThis(aInitialThis, aInterfaceInfo, aMethodIndex, aHideFirstParamFromJS, aIIDOfResult, _retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPCFunctionThisTranslator : public nsIXPCFunctionThisTranslator
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCFUNCTIONTHISTRANSLATOR

  nsXPCFunctionThisTranslator();

private:
  ~nsXPCFunctionThisTranslator();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPCFunctionThisTranslator, nsIXPCFunctionThisTranslator)

nsXPCFunctionThisTranslator::nsXPCFunctionThisTranslator()
{
  /* member initializers and constructor code */
}

nsXPCFunctionThisTranslator::~nsXPCFunctionThisTranslator()
{
  /* destructor code */
}

/* nsISupports TranslateThis (in nsISupports aInitialThis, in nsIInterfaceInfo aInterfaceInfo, in PRUint16 aMethodIndex, out PRBool aHideFirstParamFromJS, out nsIIDPtr aIIDOfResult); */
NS_IMETHODIMP nsXPCFunctionThisTranslator::TranslateThis(nsISupports *aInitialThis, nsIInterfaceInfo *aInterfaceInfo, PRUint16 aMethodIndex, PRBool *aHideFirstParamFromJS, nsIID * *aIIDOfResult, nsISupports **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif

// For use with the service manager
// {CB6593E0-F9B2-11d2-BDD6-000064657374}
#define NS_XPCONNECT_CID \
{ 0xcb6593e0, 0xf9b2, 0x11d2, \
    { 0xbd, 0xd6, 0x0, 0x0, 0x64, 0x65, 0x73, 0x74 } }

/* starting interface:    nsIXPConnect */
#define NS_IXPCONNECT_IID_STR "deb1d48e-7469-4b01-b186-d9854c7d3f2d"

#define NS_IXPCONNECT_IID \
  {0xdeb1d48e, 0x7469, 0x4b01, \
    { 0xb1, 0x86, 0xd9, 0x85, 0x4c, 0x7d, 0x3f, 0x2d }}

class nsIXPConnect : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCONNECT_IID)

  NS_DEFINE_STATIC_CID_ACCESSOR(NS_XPCONNECT_CID)
  /***************************************************************************/
  /* void initClasses (in JSContextPtr aJSContext, in JSObjectPtr aGlobalJSObj); */
  NS_IMETHOD InitClasses(JSContext * aJSContext, JSObject * aGlobalJSObj) = 0;

  /* nsIXPConnectJSObjectHolder initClassesWithNewWrappedGlobal (in JSContextPtr aJSContext, in nsISupports aCOMObj, in nsIIDRef aIID, in PRUint32 aFlags); */
  NS_IMETHOD InitClassesWithNewWrappedGlobal(JSContext * aJSContext, nsISupports *aCOMObj, const nsIID & aIID, PRUint32 aFlags, nsIXPConnectJSObjectHolder **_retval) = 0;

  enum { INIT_JS_STANDARD_CLASSES = 1U };

  enum { FLAG_SYSTEM_GLOBAL_OBJECT = 2U };

  /**
    * wrapNative will create a new JSObject or return an existing one.
    *
    * The JSObject is returned inside a refcounted nsIXPConnectJSObjectHolder.
    * As long as this holder is held the JSObject will be protected from
    * collection by JavaScript's garbage collector. It is a good idea to
    * transfer the JSObject to some equally protected place before releasing
    * the holder (i.e. use JS_SetProperty to make this object a property of
    * some other JSObject).
    *
    * This method now correctly deals with cases where the passed in xpcom
    * object already has an associated JSObject for the cases:
    *  1) The xpcom object has already been wrapped for use in the same scope
    *     as an nsIXPConnectWrappedNative.
    *  2) The xpcom object is in fact a nsIXPConnectWrappedJS and thus already
    *     has an underlying JSObject.
    *  3) The xpcom object implements nsIScriptObjectOwner; i.e. is an idlc
    *     style DOM object for which we can call GetScriptObject to get the
    *     JSObject it uses to represent itself into JavaScript.
    *
    * It *might* be possible to QueryInterface the nsIXPConnectJSObjectHolder
    * returned by the method into a nsIXPConnectWrappedNative or a
    * nsIXPConnectWrappedJS.
    *
    * This method will never wrap the JSObject involved in an
    * XPCNativeWrapper before returning.
    *
    * Returns:
    *    success:
    *       NS_OK
    *    failure:
    *       NS_ERROR_XPC_BAD_CONVERT_NATIVE
    *       NS_ERROR_XPC_CANT_GET_JSOBJECT_OF_DOM_OBJECT
    *       NS_ERROR_FAILURE
    */
  /* nsIXPConnectJSObjectHolder wrapNative (in JSContextPtr aJSContext, in JSObjectPtr aScope, in nsISupports aCOMObj, in nsIIDRef aIID); */
  NS_IMETHOD WrapNative(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectJSObjectHolder **_retval) = 0;

  /**
    * wrapJS will yield a new or previously existing xpcom interface pointer
    * to represent the JSObject passed in.
    *
    * This method now correctly deals with cases where the passed in JSObject
    * already has an associated xpcom interface for the cases:
    *  1) The JSObject has already been wrapped as a nsIXPConnectWrappedJS.
    *  2) The JSObject is in fact a nsIXPConnectWrappedNative and thus already
    *     has an underlying xpcom object.
    *  3) The JSObject is of a jsclass which supports getting the nsISupports
    *     from the JSObject directly. This is used for idlc style objects
    *     (e.g. DOM objects).
    *
    * It *might* be possible to QueryInterface the resulting interface pointer
    * to nsIXPConnectWrappedJS.
    *
    * Returns:
    *   success:
    *     NS_OK
    *    failure:
    *       NS_ERROR_XPC_BAD_CONVERT_JS
    *       NS_ERROR_FAILURE
    */
  /* void wrapJS (in JSContextPtr aJSContext, in JSObjectPtr aJSObj, in nsIIDRef aIID, [iid_is (aIID), retval] out nsQIResult result); */
  NS_IMETHOD WrapJS(JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result) = 0;

  /**
    * This only succeeds if the JSObject is a nsIXPConnectWrappedNative.
    * A new wrapper is *never* constructed.
    */
  /* nsIXPConnectWrappedNative getWrappedNativeOfJSObject (in JSContextPtr aJSContext, in JSObjectPtr aJSObj); */
  NS_IMETHOD GetWrappedNativeOfJSObject(JSContext * aJSContext, JSObject * aJSObj, nsIXPConnectWrappedNative **_retval) = 0;

  /* void setSecurityManagerForJSContext (in JSContextPtr aJSContext, in nsIXPCSecurityManager aManager, in PRUint16 flags); */
  NS_IMETHOD SetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager *aManager, PRUint16 flags) = 0;

  /* void getSecurityManagerForJSContext (in JSContextPtr aJSContext, out nsIXPCSecurityManager aManager, out PRUint16 flags); */
  NS_IMETHOD GetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager **aManager, PRUint16 *flags) = 0;

  /**
    * The security manager to use when the current JSContext has no security
    * manager.
    */
  /* void setDefaultSecurityManager (in nsIXPCSecurityManager aManager, in PRUint16 flags); */
  NS_IMETHOD SetDefaultSecurityManager(nsIXPCSecurityManager *aManager, PRUint16 flags) = 0;

  /* void getDefaultSecurityManager (out nsIXPCSecurityManager aManager, out PRUint16 flags); */
  NS_IMETHOD GetDefaultSecurityManager(nsIXPCSecurityManager **aManager, PRUint16 *flags) = 0;

  /* nsIStackFrame createStackFrameLocation (in PRUint32 aLanguage, in string aFilename, in string aFunctionName, in PRInt32 aLineNumber, in nsIStackFrame aCaller); */
  NS_IMETHOD CreateStackFrameLocation(PRUint32 aLanguage, const char *aFilename, const char *aFunctionName, PRInt32 aLineNumber, nsIStackFrame *aCaller, nsIStackFrame **_retval) = 0;

  /**
    * XPConnect builds internal objects that parallel, and are one-to-one with,
    * the JSContexts in the JSRuntime. It builds these objects as needed.
    * This method tells XPConnect to resynchronize its representations with the
    * list of JSContexts currently 'alive' in the JSRuntime. This allows it
    * to cleanup any representations of JSContexts that are no longer valid.
    */
  /* void syncJSContexts (); */
  NS_IMETHOD SyncJSContexts(void) = 0;

  /* readonly attribute nsIStackFrame CurrentJSStack; */
  NS_IMETHOD GetCurrentJSStack(nsIStackFrame * *aCurrentJSStack) = 0;

  /* readonly attribute nsIXPCNativeCallContext CurrentNativeCallContext; */
  NS_IMETHOD GetCurrentNativeCallContext(nsIXPCNativeCallContext * *aCurrentNativeCallContext) = 0;

  /* attribute nsIException PendingException; */
  NS_IMETHOD GetPendingException(nsIException * *aPendingException) = 0;
  NS_IMETHOD SetPendingException(nsIException * aPendingException) = 0;

  /* void debugDump (in short depth); */
  NS_IMETHOD DebugDump(PRInt16 depth) = 0;

  /* void debugDumpObject (in nsISupports aCOMObj, in short depth); */
  NS_IMETHOD DebugDumpObject(nsISupports *aCOMObj, PRInt16 depth) = 0;

  /* void debugDumpJSStack (in PRBool showArgs, in PRBool showLocals, in PRBool showThisProps); */
  NS_IMETHOD DebugDumpJSStack(PRBool showArgs, PRBool showLocals, PRBool showThisProps) = 0;

  /* void debugDumpEvalInJSStackFrame (in PRUint32 aFrameNumber, in string aSourceText); */
  NS_IMETHOD DebugDumpEvalInJSStackFrame(PRUint32 aFrameNumber, const char *aSourceText) = 0;

  /**
    * Set fallback JSContext to use when xpconnect can't find an appropriate
    * context to use to execute JavaScript.
    *
    * NOTE: This method is DEPRECATED. 
    *       Use nsIThreadJSContextStack::safeJSContext instead.
    */
  /* void setSafeJSContextForCurrentThread (in JSContextPtr cx); */
  NS_IMETHOD SetSafeJSContextForCurrentThread(JSContext * cx) = 0;

  /**
    * wrapJSAggregatedToNative is just like wrapJS except it is used in cases
    * where the JSObject is also aggregated to some native xpcom Object.
    * At present XBL is the only system that might want to do this.
    *
    * XXX write more!
    *
    * Returns:
    *   success:
    *     NS_OK
    *    failure:
    *       NS_ERROR_XPC_BAD_CONVERT_JS
    *       NS_ERROR_FAILURE
    */
  /* void wrapJSAggregatedToNative (in nsISupports aOuter, in JSContextPtr aJSContext, in JSObjectPtr aJSObj, in nsIIDRef aIID, [iid_is (aIID), retval] out nsQIResult result); */
  NS_IMETHOD WrapJSAggregatedToNative(nsISupports *aOuter, JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result) = 0;

  /**
    * This only succeeds if the native object is already wrapped by xpconnect.
    * A new wrapper is *never* constructed.
    */
  /* nsIXPConnectWrappedNative getWrappedNativeOfNativeObject (in JSContextPtr aJSContext, in JSObjectPtr aScope, in nsISupports aCOMObj, in nsIIDRef aIID); */
  NS_IMETHOD GetWrappedNativeOfNativeObject(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectWrappedNative **_retval) = 0;

  /* nsIXPCFunctionThisTranslator getFunctionThisTranslator (in nsIIDRef aIID); */
  NS_IMETHOD GetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator **_retval) = 0;

  /* nsIXPCFunctionThisTranslator setFunctionThisTranslator (in nsIIDRef aIID, in nsIXPCFunctionThisTranslator aTranslator); */
  NS_IMETHOD SetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator *aTranslator, nsIXPCFunctionThisTranslator **_retval) = 0;

  /* nsIXPConnectJSObjectHolder reparentWrappedNativeIfFound (in JSContextPtr aJSContext, in JSObjectPtr aScope, in JSObjectPtr aNewParent, in nsISupports aCOMObj); */
  NS_IMETHOD ReparentWrappedNativeIfFound(JSContext * aJSContext, JSObject * aScope, JSObject * aNewParent, nsISupports *aCOMObj, nsIXPConnectJSObjectHolder **_retval) = 0;

  /* void clearAllWrappedNativeSecurityPolicies (); */
  NS_IMETHOD ClearAllWrappedNativeSecurityPolicies(void) = 0;

  /* nsIXPConnectJSObjectHolder getWrappedNativePrototype (in JSContextPtr aJSContext, in JSObjectPtr aScope, in nsIClassInfo aClassInfo); */
  NS_IMETHOD GetWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder **_retval) = 0;

  /* attribute PRBool collectGarbageOnMainThreadOnly; */
  NS_IMETHOD GetCollectGarbageOnMainThreadOnly(PRBool *aCollectGarbageOnMainThreadOnly) = 0;
  NS_IMETHOD SetCollectGarbageOnMainThreadOnly(PRBool aCollectGarbageOnMainThreadOnly) = 0;

  /* attribute PRBool deferReleasesUntilAfterGarbageCollection; */
  NS_IMETHOD GetDeferReleasesUntilAfterGarbageCollection(PRBool *aDeferReleasesUntilAfterGarbageCollection) = 0;
  NS_IMETHOD SetDeferReleasesUntilAfterGarbageCollection(PRBool aDeferReleasesUntilAfterGarbageCollection) = 0;

  /* void releaseJSContext (in JSContextPtr aJSContext, in PRBool noGC); */
  NS_IMETHOD ReleaseJSContext(JSContext * aJSContext, PRBool noGC) = 0;

  /* JSVal variantToJS (in JSContextPtr ctx, in JSObjectPtr scope, in nsIVariant value); */
  NS_IMETHOD VariantToJS(JSContext * ctx, JSObject * scope, nsIVariant *value, jsval *_retval) = 0;

  /* nsIVariant JSToVariant (in JSContextPtr ctx, in JSVal value); */
  NS_IMETHOD JSToVariant(JSContext * ctx, jsval value, nsIVariant **_retval) = 0;

  /**
     * Preconfigure XPCNativeWrapper automation so that when a scripted
     * caller whose filename starts with filenamePrefix accesses a wrapped
     * native that is not flagged as "system", the wrapped native will be
     * automatically wrapped with an XPCNativeWrapper.
     *
     * @param aFilenamePrefix the UTF-8 filename prefix to match, which
     *                        should end with a slash (/) character
     */
  /* void flagSystemFilenamePrefix (in string aFilenamePrefix); */
  NS_IMETHOD FlagSystemFilenamePrefix(const char *aFilenamePrefix) = 0;

  /**
     * Restore an old prototype for wrapped natives of type
     * aClassInfo. This should be used only when restoring an old
     * scope into a state close to where it was prior to
     * being reinitialized.
     */
  /* void restoreWrappedNativePrototype (in JSContextPtr aJSContext, in JSObjectPtr aScope, in nsIClassInfo aClassInfo, in nsIXPConnectJSObjectHolder aPrototype); */
  NS_IMETHOD RestoreWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder *aPrototype) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCONNECT \
  NS_IMETHOD InitClasses(JSContext * aJSContext, JSObject * aGlobalJSObj); \
  NS_IMETHOD InitClassesWithNewWrappedGlobal(JSContext * aJSContext, nsISupports *aCOMObj, const nsIID & aIID, PRUint32 aFlags, nsIXPConnectJSObjectHolder **_retval); \
  NS_IMETHOD WrapNative(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectJSObjectHolder **_retval); \
  NS_IMETHOD WrapJS(JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result); \
  NS_IMETHOD GetWrappedNativeOfJSObject(JSContext * aJSContext, JSObject * aJSObj, nsIXPConnectWrappedNative **_retval); \
  NS_IMETHOD SetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager *aManager, PRUint16 flags); \
  NS_IMETHOD GetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager **aManager, PRUint16 *flags); \
  NS_IMETHOD SetDefaultSecurityManager(nsIXPCSecurityManager *aManager, PRUint16 flags); \
  NS_IMETHOD GetDefaultSecurityManager(nsIXPCSecurityManager **aManager, PRUint16 *flags); \
  NS_IMETHOD CreateStackFrameLocation(PRUint32 aLanguage, const char *aFilename, const char *aFunctionName, PRInt32 aLineNumber, nsIStackFrame *aCaller, nsIStackFrame **_retval); \
  NS_IMETHOD SyncJSContexts(void); \
  NS_IMETHOD GetCurrentJSStack(nsIStackFrame * *aCurrentJSStack); \
  NS_IMETHOD GetCurrentNativeCallContext(nsIXPCNativeCallContext * *aCurrentNativeCallContext); \
  NS_IMETHOD GetPendingException(nsIException * *aPendingException); \
  NS_IMETHOD SetPendingException(nsIException * aPendingException); \
  NS_IMETHOD DebugDump(PRInt16 depth); \
  NS_IMETHOD DebugDumpObject(nsISupports *aCOMObj, PRInt16 depth); \
  NS_IMETHOD DebugDumpJSStack(PRBool showArgs, PRBool showLocals, PRBool showThisProps); \
  NS_IMETHOD DebugDumpEvalInJSStackFrame(PRUint32 aFrameNumber, const char *aSourceText); \
  NS_IMETHOD SetSafeJSContextForCurrentThread(JSContext * cx); \
  NS_IMETHOD WrapJSAggregatedToNative(nsISupports *aOuter, JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result); \
  NS_IMETHOD GetWrappedNativeOfNativeObject(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectWrappedNative **_retval); \
  NS_IMETHOD GetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator **_retval); \
  NS_IMETHOD SetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator *aTranslator, nsIXPCFunctionThisTranslator **_retval); \
  NS_IMETHOD ReparentWrappedNativeIfFound(JSContext * aJSContext, JSObject * aScope, JSObject * aNewParent, nsISupports *aCOMObj, nsIXPConnectJSObjectHolder **_retval); \
  NS_IMETHOD ClearAllWrappedNativeSecurityPolicies(void); \
  NS_IMETHOD GetWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder **_retval); \
  NS_IMETHOD GetCollectGarbageOnMainThreadOnly(PRBool *aCollectGarbageOnMainThreadOnly); \
  NS_IMETHOD SetCollectGarbageOnMainThreadOnly(PRBool aCollectGarbageOnMainThreadOnly); \
  NS_IMETHOD GetDeferReleasesUntilAfterGarbageCollection(PRBool *aDeferReleasesUntilAfterGarbageCollection); \
  NS_IMETHOD SetDeferReleasesUntilAfterGarbageCollection(PRBool aDeferReleasesUntilAfterGarbageCollection); \
  NS_IMETHOD ReleaseJSContext(JSContext * aJSContext, PRBool noGC); \
  NS_IMETHOD VariantToJS(JSContext * ctx, JSObject * scope, nsIVariant *value, jsval *_retval); \
  NS_IMETHOD JSToVariant(JSContext * ctx, jsval value, nsIVariant **_retval); \
  NS_IMETHOD FlagSystemFilenamePrefix(const char *aFilenamePrefix); \
  NS_IMETHOD RestoreWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder *aPrototype); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCONNECT(_to) \
  NS_IMETHOD InitClasses(JSContext * aJSContext, JSObject * aGlobalJSObj) { return _to InitClasses(aJSContext, aGlobalJSObj); } \
  NS_IMETHOD InitClassesWithNewWrappedGlobal(JSContext * aJSContext, nsISupports *aCOMObj, const nsIID & aIID, PRUint32 aFlags, nsIXPConnectJSObjectHolder **_retval) { return _to InitClassesWithNewWrappedGlobal(aJSContext, aCOMObj, aIID, aFlags, _retval); } \
  NS_IMETHOD WrapNative(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectJSObjectHolder **_retval) { return _to WrapNative(aJSContext, aScope, aCOMObj, aIID, _retval); } \
  NS_IMETHOD WrapJS(JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result) { return _to WrapJS(aJSContext, aJSObj, aIID, result); } \
  NS_IMETHOD GetWrappedNativeOfJSObject(JSContext * aJSContext, JSObject * aJSObj, nsIXPConnectWrappedNative **_retval) { return _to GetWrappedNativeOfJSObject(aJSContext, aJSObj, _retval); } \
  NS_IMETHOD SetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager *aManager, PRUint16 flags) { return _to SetSecurityManagerForJSContext(aJSContext, aManager, flags); } \
  NS_IMETHOD GetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager **aManager, PRUint16 *flags) { return _to GetSecurityManagerForJSContext(aJSContext, aManager, flags); } \
  NS_IMETHOD SetDefaultSecurityManager(nsIXPCSecurityManager *aManager, PRUint16 flags) { return _to SetDefaultSecurityManager(aManager, flags); } \
  NS_IMETHOD GetDefaultSecurityManager(nsIXPCSecurityManager **aManager, PRUint16 *flags) { return _to GetDefaultSecurityManager(aManager, flags); } \
  NS_IMETHOD CreateStackFrameLocation(PRUint32 aLanguage, const char *aFilename, const char *aFunctionName, PRInt32 aLineNumber, nsIStackFrame *aCaller, nsIStackFrame **_retval) { return _to CreateStackFrameLocation(aLanguage, aFilename, aFunctionName, aLineNumber, aCaller, _retval); } \
  NS_IMETHOD SyncJSContexts(void) { return _to SyncJSContexts(); } \
  NS_IMETHOD GetCurrentJSStack(nsIStackFrame * *aCurrentJSStack) { return _to GetCurrentJSStack(aCurrentJSStack); } \
  NS_IMETHOD GetCurrentNativeCallContext(nsIXPCNativeCallContext * *aCurrentNativeCallContext) { return _to GetCurrentNativeCallContext(aCurrentNativeCallContext); } \
  NS_IMETHOD GetPendingException(nsIException * *aPendingException) { return _to GetPendingException(aPendingException); } \
  NS_IMETHOD SetPendingException(nsIException * aPendingException) { return _to SetPendingException(aPendingException); } \
  NS_IMETHOD DebugDump(PRInt16 depth) { return _to DebugDump(depth); } \
  NS_IMETHOD DebugDumpObject(nsISupports *aCOMObj, PRInt16 depth) { return _to DebugDumpObject(aCOMObj, depth); } \
  NS_IMETHOD DebugDumpJSStack(PRBool showArgs, PRBool showLocals, PRBool showThisProps) { return _to DebugDumpJSStack(showArgs, showLocals, showThisProps); } \
  NS_IMETHOD DebugDumpEvalInJSStackFrame(PRUint32 aFrameNumber, const char *aSourceText) { return _to DebugDumpEvalInJSStackFrame(aFrameNumber, aSourceText); } \
  NS_IMETHOD SetSafeJSContextForCurrentThread(JSContext * cx) { return _to SetSafeJSContextForCurrentThread(cx); } \
  NS_IMETHOD WrapJSAggregatedToNative(nsISupports *aOuter, JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result) { return _to WrapJSAggregatedToNative(aOuter, aJSContext, aJSObj, aIID, result); } \
  NS_IMETHOD GetWrappedNativeOfNativeObject(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectWrappedNative **_retval) { return _to GetWrappedNativeOfNativeObject(aJSContext, aScope, aCOMObj, aIID, _retval); } \
  NS_IMETHOD GetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator **_retval) { return _to GetFunctionThisTranslator(aIID, _retval); } \
  NS_IMETHOD SetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator *aTranslator, nsIXPCFunctionThisTranslator **_retval) { return _to SetFunctionThisTranslator(aIID, aTranslator, _retval); } \
  NS_IMETHOD ReparentWrappedNativeIfFound(JSContext * aJSContext, JSObject * aScope, JSObject * aNewParent, nsISupports *aCOMObj, nsIXPConnectJSObjectHolder **_retval) { return _to ReparentWrappedNativeIfFound(aJSContext, aScope, aNewParent, aCOMObj, _retval); } \
  NS_IMETHOD ClearAllWrappedNativeSecurityPolicies(void) { return _to ClearAllWrappedNativeSecurityPolicies(); } \
  NS_IMETHOD GetWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder **_retval) { return _to GetWrappedNativePrototype(aJSContext, aScope, aClassInfo, _retval); } \
  NS_IMETHOD GetCollectGarbageOnMainThreadOnly(PRBool *aCollectGarbageOnMainThreadOnly) { return _to GetCollectGarbageOnMainThreadOnly(aCollectGarbageOnMainThreadOnly); } \
  NS_IMETHOD SetCollectGarbageOnMainThreadOnly(PRBool aCollectGarbageOnMainThreadOnly) { return _to SetCollectGarbageOnMainThreadOnly(aCollectGarbageOnMainThreadOnly); } \
  NS_IMETHOD GetDeferReleasesUntilAfterGarbageCollection(PRBool *aDeferReleasesUntilAfterGarbageCollection) { return _to GetDeferReleasesUntilAfterGarbageCollection(aDeferReleasesUntilAfterGarbageCollection); } \
  NS_IMETHOD SetDeferReleasesUntilAfterGarbageCollection(PRBool aDeferReleasesUntilAfterGarbageCollection) { return _to SetDeferReleasesUntilAfterGarbageCollection(aDeferReleasesUntilAfterGarbageCollection); } \
  NS_IMETHOD ReleaseJSContext(JSContext * aJSContext, PRBool noGC) { return _to ReleaseJSContext(aJSContext, noGC); } \
  NS_IMETHOD VariantToJS(JSContext * ctx, JSObject * scope, nsIVariant *value, jsval *_retval) { return _to VariantToJS(ctx, scope, value, _retval); } \
  NS_IMETHOD JSToVariant(JSContext * ctx, jsval value, nsIVariant **_retval) { return _to JSToVariant(ctx, value, _retval); } \
  NS_IMETHOD FlagSystemFilenamePrefix(const char *aFilenamePrefix) { return _to FlagSystemFilenamePrefix(aFilenamePrefix); } \
  NS_IMETHOD RestoreWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder *aPrototype) { return _to RestoreWrappedNativePrototype(aJSContext, aScope, aClassInfo, aPrototype); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCONNECT(_to) \
  NS_IMETHOD InitClasses(JSContext * aJSContext, JSObject * aGlobalJSObj) { return !_to ? NS_ERROR_NULL_POINTER : _to->InitClasses(aJSContext, aGlobalJSObj); } \
  NS_IMETHOD InitClassesWithNewWrappedGlobal(JSContext * aJSContext, nsISupports *aCOMObj, const nsIID & aIID, PRUint32 aFlags, nsIXPConnectJSObjectHolder **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->InitClassesWithNewWrappedGlobal(aJSContext, aCOMObj, aIID, aFlags, _retval); } \
  NS_IMETHOD WrapNative(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectJSObjectHolder **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->WrapNative(aJSContext, aScope, aCOMObj, aIID, _retval); } \
  NS_IMETHOD WrapJS(JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result) { return !_to ? NS_ERROR_NULL_POINTER : _to->WrapJS(aJSContext, aJSObj, aIID, result); } \
  NS_IMETHOD GetWrappedNativeOfJSObject(JSContext * aJSContext, JSObject * aJSObj, nsIXPConnectWrappedNative **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetWrappedNativeOfJSObject(aJSContext, aJSObj, _retval); } \
  NS_IMETHOD SetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager *aManager, PRUint16 flags) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetSecurityManagerForJSContext(aJSContext, aManager, flags); } \
  NS_IMETHOD GetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager **aManager, PRUint16 *flags) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetSecurityManagerForJSContext(aJSContext, aManager, flags); } \
  NS_IMETHOD SetDefaultSecurityManager(nsIXPCSecurityManager *aManager, PRUint16 flags) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetDefaultSecurityManager(aManager, flags); } \
  NS_IMETHOD GetDefaultSecurityManager(nsIXPCSecurityManager **aManager, PRUint16 *flags) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetDefaultSecurityManager(aManager, flags); } \
  NS_IMETHOD CreateStackFrameLocation(PRUint32 aLanguage, const char *aFilename, const char *aFunctionName, PRInt32 aLineNumber, nsIStackFrame *aCaller, nsIStackFrame **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->CreateStackFrameLocation(aLanguage, aFilename, aFunctionName, aLineNumber, aCaller, _retval); } \
  NS_IMETHOD SyncJSContexts(void) { return !_to ? NS_ERROR_NULL_POINTER : _to->SyncJSContexts(); } \
  NS_IMETHOD GetCurrentJSStack(nsIStackFrame * *aCurrentJSStack) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCurrentJSStack(aCurrentJSStack); } \
  NS_IMETHOD GetCurrentNativeCallContext(nsIXPCNativeCallContext * *aCurrentNativeCallContext) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCurrentNativeCallContext(aCurrentNativeCallContext); } \
  NS_IMETHOD GetPendingException(nsIException * *aPendingException) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetPendingException(aPendingException); } \
  NS_IMETHOD SetPendingException(nsIException * aPendingException) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetPendingException(aPendingException); } \
  NS_IMETHOD DebugDump(PRInt16 depth) { return !_to ? NS_ERROR_NULL_POINTER : _to->DebugDump(depth); } \
  NS_IMETHOD DebugDumpObject(nsISupports *aCOMObj, PRInt16 depth) { return !_to ? NS_ERROR_NULL_POINTER : _to->DebugDumpObject(aCOMObj, depth); } \
  NS_IMETHOD DebugDumpJSStack(PRBool showArgs, PRBool showLocals, PRBool showThisProps) { return !_to ? NS_ERROR_NULL_POINTER : _to->DebugDumpJSStack(showArgs, showLocals, showThisProps); } \
  NS_IMETHOD DebugDumpEvalInJSStackFrame(PRUint32 aFrameNumber, const char *aSourceText) { return !_to ? NS_ERROR_NULL_POINTER : _to->DebugDumpEvalInJSStackFrame(aFrameNumber, aSourceText); } \
  NS_IMETHOD SetSafeJSContextForCurrentThread(JSContext * cx) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetSafeJSContextForCurrentThread(cx); } \
  NS_IMETHOD WrapJSAggregatedToNative(nsISupports *aOuter, JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result) { return !_to ? NS_ERROR_NULL_POINTER : _to->WrapJSAggregatedToNative(aOuter, aJSContext, aJSObj, aIID, result); } \
  NS_IMETHOD GetWrappedNativeOfNativeObject(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectWrappedNative **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetWrappedNativeOfNativeObject(aJSContext, aScope, aCOMObj, aIID, _retval); } \
  NS_IMETHOD GetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetFunctionThisTranslator(aIID, _retval); } \
  NS_IMETHOD SetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator *aTranslator, nsIXPCFunctionThisTranslator **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetFunctionThisTranslator(aIID, aTranslator, _retval); } \
  NS_IMETHOD ReparentWrappedNativeIfFound(JSContext * aJSContext, JSObject * aScope, JSObject * aNewParent, nsISupports *aCOMObj, nsIXPConnectJSObjectHolder **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->ReparentWrappedNativeIfFound(aJSContext, aScope, aNewParent, aCOMObj, _retval); } \
  NS_IMETHOD ClearAllWrappedNativeSecurityPolicies(void) { return !_to ? NS_ERROR_NULL_POINTER : _to->ClearAllWrappedNativeSecurityPolicies(); } \
  NS_IMETHOD GetWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetWrappedNativePrototype(aJSContext, aScope, aClassInfo, _retval); } \
  NS_IMETHOD GetCollectGarbageOnMainThreadOnly(PRBool *aCollectGarbageOnMainThreadOnly) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCollectGarbageOnMainThreadOnly(aCollectGarbageOnMainThreadOnly); } \
  NS_IMETHOD SetCollectGarbageOnMainThreadOnly(PRBool aCollectGarbageOnMainThreadOnly) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetCollectGarbageOnMainThreadOnly(aCollectGarbageOnMainThreadOnly); } \
  NS_IMETHOD GetDeferReleasesUntilAfterGarbageCollection(PRBool *aDeferReleasesUntilAfterGarbageCollection) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetDeferReleasesUntilAfterGarbageCollection(aDeferReleasesUntilAfterGarbageCollection); } \
  NS_IMETHOD SetDeferReleasesUntilAfterGarbageCollection(PRBool aDeferReleasesUntilAfterGarbageCollection) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetDeferReleasesUntilAfterGarbageCollection(aDeferReleasesUntilAfterGarbageCollection); } \
  NS_IMETHOD ReleaseJSContext(JSContext * aJSContext, PRBool noGC) { return !_to ? NS_ERROR_NULL_POINTER : _to->ReleaseJSContext(aJSContext, noGC); } \
  NS_IMETHOD VariantToJS(JSContext * ctx, JSObject * scope, nsIVariant *value, jsval *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->VariantToJS(ctx, scope, value, _retval); } \
  NS_IMETHOD JSToVariant(JSContext * ctx, jsval value, nsIVariant **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->JSToVariant(ctx, value, _retval); } \
  NS_IMETHOD FlagSystemFilenamePrefix(const char *aFilenamePrefix) { return !_to ? NS_ERROR_NULL_POINTER : _to->FlagSystemFilenamePrefix(aFilenamePrefix); } \
  NS_IMETHOD RestoreWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder *aPrototype) { return !_to ? NS_ERROR_NULL_POINTER : _to->RestoreWrappedNativePrototype(aJSContext, aScope, aClassInfo, aPrototype); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPConnect : public nsIXPConnect
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCONNECT

  nsXPConnect();

private:
  ~nsXPConnect();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPConnect, nsIXPConnect)

nsXPConnect::nsXPConnect()
{
  /* member initializers and constructor code */
}

nsXPConnect::~nsXPConnect()
{
  /* destructor code */
}

/* void initClasses (in JSContextPtr aJSContext, in JSObjectPtr aGlobalJSObj); */
NS_IMETHODIMP nsXPConnect::InitClasses(JSContext * aJSContext, JSObject * aGlobalJSObj)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIXPConnectJSObjectHolder initClassesWithNewWrappedGlobal (in JSContextPtr aJSContext, in nsISupports aCOMObj, in nsIIDRef aIID, in PRUint32 aFlags); */
NS_IMETHODIMP nsXPConnect::InitClassesWithNewWrappedGlobal(JSContext * aJSContext, nsISupports *aCOMObj, const nsIID & aIID, PRUint32 aFlags, nsIXPConnectJSObjectHolder **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIXPConnectJSObjectHolder wrapNative (in JSContextPtr aJSContext, in JSObjectPtr aScope, in nsISupports aCOMObj, in nsIIDRef aIID); */
NS_IMETHODIMP nsXPConnect::WrapNative(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectJSObjectHolder **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void wrapJS (in JSContextPtr aJSContext, in JSObjectPtr aJSObj, in nsIIDRef aIID, [iid_is (aIID), retval] out nsQIResult result); */
NS_IMETHODIMP nsXPConnect::WrapJS(JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIXPConnectWrappedNative getWrappedNativeOfJSObject (in JSContextPtr aJSContext, in JSObjectPtr aJSObj); */
NS_IMETHODIMP nsXPConnect::GetWrappedNativeOfJSObject(JSContext * aJSContext, JSObject * aJSObj, nsIXPConnectWrappedNative **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void setSecurityManagerForJSContext (in JSContextPtr aJSContext, in nsIXPCSecurityManager aManager, in PRUint16 flags); */
NS_IMETHODIMP nsXPConnect::SetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager *aManager, PRUint16 flags)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void getSecurityManagerForJSContext (in JSContextPtr aJSContext, out nsIXPCSecurityManager aManager, out PRUint16 flags); */
NS_IMETHODIMP nsXPConnect::GetSecurityManagerForJSContext(JSContext * aJSContext, nsIXPCSecurityManager **aManager, PRUint16 *flags)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void setDefaultSecurityManager (in nsIXPCSecurityManager aManager, in PRUint16 flags); */
NS_IMETHODIMP nsXPConnect::SetDefaultSecurityManager(nsIXPCSecurityManager *aManager, PRUint16 flags)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void getDefaultSecurityManager (out nsIXPCSecurityManager aManager, out PRUint16 flags); */
NS_IMETHODIMP nsXPConnect::GetDefaultSecurityManager(nsIXPCSecurityManager **aManager, PRUint16 *flags)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIStackFrame createStackFrameLocation (in PRUint32 aLanguage, in string aFilename, in string aFunctionName, in PRInt32 aLineNumber, in nsIStackFrame aCaller); */
NS_IMETHODIMP nsXPConnect::CreateStackFrameLocation(PRUint32 aLanguage, const char *aFilename, const char *aFunctionName, PRInt32 aLineNumber, nsIStackFrame *aCaller, nsIStackFrame **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void syncJSContexts (); */
NS_IMETHODIMP nsXPConnect::SyncJSContexts()
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsIStackFrame CurrentJSStack; */
NS_IMETHODIMP nsXPConnect::GetCurrentJSStack(nsIStackFrame * *aCurrentJSStack)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsIXPCNativeCallContext CurrentNativeCallContext; */
NS_IMETHODIMP nsXPConnect::GetCurrentNativeCallContext(nsIXPCNativeCallContext * *aCurrentNativeCallContext)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute nsIException PendingException; */
NS_IMETHODIMP nsXPConnect::GetPendingException(nsIException * *aPendingException)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsXPConnect::SetPendingException(nsIException * aPendingException)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void debugDump (in short depth); */
NS_IMETHODIMP nsXPConnect::DebugDump(PRInt16 depth)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void debugDumpObject (in nsISupports aCOMObj, in short depth); */
NS_IMETHODIMP nsXPConnect::DebugDumpObject(nsISupports *aCOMObj, PRInt16 depth)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void debugDumpJSStack (in PRBool showArgs, in PRBool showLocals, in PRBool showThisProps); */
NS_IMETHODIMP nsXPConnect::DebugDumpJSStack(PRBool showArgs, PRBool showLocals, PRBool showThisProps)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void debugDumpEvalInJSStackFrame (in PRUint32 aFrameNumber, in string aSourceText); */
NS_IMETHODIMP nsXPConnect::DebugDumpEvalInJSStackFrame(PRUint32 aFrameNumber, const char *aSourceText)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void setSafeJSContextForCurrentThread (in JSContextPtr cx); */
NS_IMETHODIMP nsXPConnect::SetSafeJSContextForCurrentThread(JSContext * cx)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void wrapJSAggregatedToNative (in nsISupports aOuter, in JSContextPtr aJSContext, in JSObjectPtr aJSObj, in nsIIDRef aIID, [iid_is (aIID), retval] out nsQIResult result); */
NS_IMETHODIMP nsXPConnect::WrapJSAggregatedToNative(nsISupports *aOuter, JSContext * aJSContext, JSObject * aJSObj, const nsIID & aIID, void * *result)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIXPConnectWrappedNative getWrappedNativeOfNativeObject (in JSContextPtr aJSContext, in JSObjectPtr aScope, in nsISupports aCOMObj, in nsIIDRef aIID); */
NS_IMETHODIMP nsXPConnect::GetWrappedNativeOfNativeObject(JSContext * aJSContext, JSObject * aScope, nsISupports *aCOMObj, const nsIID & aIID, nsIXPConnectWrappedNative **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIXPCFunctionThisTranslator getFunctionThisTranslator (in nsIIDRef aIID); */
NS_IMETHODIMP nsXPConnect::GetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIXPCFunctionThisTranslator setFunctionThisTranslator (in nsIIDRef aIID, in nsIXPCFunctionThisTranslator aTranslator); */
NS_IMETHODIMP nsXPConnect::SetFunctionThisTranslator(const nsIID & aIID, nsIXPCFunctionThisTranslator *aTranslator, nsIXPCFunctionThisTranslator **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIXPConnectJSObjectHolder reparentWrappedNativeIfFound (in JSContextPtr aJSContext, in JSObjectPtr aScope, in JSObjectPtr aNewParent, in nsISupports aCOMObj); */
NS_IMETHODIMP nsXPConnect::ReparentWrappedNativeIfFound(JSContext * aJSContext, JSObject * aScope, JSObject * aNewParent, nsISupports *aCOMObj, nsIXPConnectJSObjectHolder **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void clearAllWrappedNativeSecurityPolicies (); */
NS_IMETHODIMP nsXPConnect::ClearAllWrappedNativeSecurityPolicies()
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIXPConnectJSObjectHolder getWrappedNativePrototype (in JSContextPtr aJSContext, in JSObjectPtr aScope, in nsIClassInfo aClassInfo); */
NS_IMETHODIMP nsXPConnect::GetWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute PRBool collectGarbageOnMainThreadOnly; */
NS_IMETHODIMP nsXPConnect::GetCollectGarbageOnMainThreadOnly(PRBool *aCollectGarbageOnMainThreadOnly)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsXPConnect::SetCollectGarbageOnMainThreadOnly(PRBool aCollectGarbageOnMainThreadOnly)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute PRBool deferReleasesUntilAfterGarbageCollection; */
NS_IMETHODIMP nsXPConnect::GetDeferReleasesUntilAfterGarbageCollection(PRBool *aDeferReleasesUntilAfterGarbageCollection)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsXPConnect::SetDeferReleasesUntilAfterGarbageCollection(PRBool aDeferReleasesUntilAfterGarbageCollection)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void releaseJSContext (in JSContextPtr aJSContext, in PRBool noGC); */
NS_IMETHODIMP nsXPConnect::ReleaseJSContext(JSContext * aJSContext, PRBool noGC)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* JSVal variantToJS (in JSContextPtr ctx, in JSObjectPtr scope, in nsIVariant value); */
NS_IMETHODIMP nsXPConnect::VariantToJS(JSContext * ctx, JSObject * scope, nsIVariant *value, jsval *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIVariant JSToVariant (in JSContextPtr ctx, in JSVal value); */
NS_IMETHODIMP nsXPConnect::JSToVariant(JSContext * ctx, jsval value, nsIVariant **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void flagSystemFilenamePrefix (in string aFilenamePrefix); */
NS_IMETHODIMP nsXPConnect::FlagSystemFilenamePrefix(const char *aFilenamePrefix)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void restoreWrappedNativePrototype (in JSContextPtr aJSContext, in JSObjectPtr aScope, in nsIClassInfo aClassInfo, in nsIXPConnectJSObjectHolder aPrototype); */
NS_IMETHODIMP nsXPConnect::RestoreWrappedNativePrototype(JSContext * aJSContext, JSObject * aScope, nsIClassInfo *aClassInfo, nsIXPConnectJSObjectHolder *aPrototype)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


/* starting interface:    nsIXPConnect_MOZILLA_1_8_BRANCH */
#define NS_IXPCONNECT_MOZILLA_1_8_BRANCH_IID_STR "4b61f818-d260-45ab-ac4e-d27790b5be5e"

#define NS_IXPCONNECT_MOZILLA_1_8_BRANCH_IID \
  {0x4b61f818, 0xd260, 0x45ab, \
    { 0xac, 0x4e, 0xd2, 0x77, 0x90, 0xb5, 0xbe, 0x5e }}

class NS_NO_VTABLE nsIXPConnect_MOZILLA_1_8_BRANCH : public nsIXPConnect {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_IXPCONNECT_MOZILLA_1_8_BRANCH_IID)

  /* void reparentScopeAwareWrappers (in JSContextPtr aJSContext, in JSObjectPtr aOldScope, in JSObjectPtr aNewScope); */
  NS_IMETHOD ReparentScopeAwareWrappers(JSContext * aJSContext, JSObject * aOldScope, JSObject * aNewScope) = 0;

  /**
    * Create a sandbox for evaluating code in isolation using
    * evalInSandboxObject().
    *
    * @param cx A context to use when creating the sandbox object.
    * @param principal The principal (or NULL to use the null principal)
    *                  to use when evaluating code in this sandbox.
    */
  /* [noscript] nsIXPConnectJSObjectHolder createSandbox (in JSContextPtr cx, in nsIPrincipal principal); */
  NS_IMETHOD CreateSandbox(JSContext * cx, nsIPrincipal *principal, nsIXPConnectJSObjectHolder **_retval) = 0;

  /**
    * Evaluate script in a sandbox, completely isolated from all
    * other running scripts.
    *
    * @param source The source of the script to evaluate.
    * @param cx The context to use when setting up the evaluation of
    *           the script. The actual evaluation will happen on a new
    *           temporary context.
    * @param sandbox The sandbox object to evaluate the script in.
    * @return The result of the evaluation as a jsval. If the caller
    *         intends to use the return value from this call the caller
    *         is responsible for rooting the jsval before making a call
    *         to this method.
    */
  /* [noscript] JSVal evalInSandboxObject (in AString source, in JSContextPtr cx, in nsIXPConnectJSObjectHolder sandbox); */
  NS_IMETHOD EvalInSandboxObject(const nsAString & source, JSContext * cx, nsIXPConnectJSObjectHolder *sandbox, jsval *_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSIXPCONNECT_MOZILLA_1_8_BRANCH \
  NS_IMETHOD ReparentScopeAwareWrappers(JSContext * aJSContext, JSObject * aOldScope, JSObject * aNewScope); \
  NS_IMETHOD CreateSandbox(JSContext * cx, nsIPrincipal *principal, nsIXPConnectJSObjectHolder **_retval); \
  NS_IMETHOD EvalInSandboxObject(const nsAString & source, JSContext * cx, nsIXPConnectJSObjectHolder *sandbox, jsval *_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSIXPCONNECT_MOZILLA_1_8_BRANCH(_to) \
  NS_IMETHOD ReparentScopeAwareWrappers(JSContext * aJSContext, JSObject * aOldScope, JSObject * aNewScope) { return _to ReparentScopeAwareWrappers(aJSContext, aOldScope, aNewScope); } \
  NS_IMETHOD CreateSandbox(JSContext * cx, nsIPrincipal *principal, nsIXPConnectJSObjectHolder **_retval) { return _to CreateSandbox(cx, principal, _retval); } \
  NS_IMETHOD EvalInSandboxObject(const nsAString & source, JSContext * cx, nsIXPConnectJSObjectHolder *sandbox, jsval *_retval) { return _to EvalInSandboxObject(source, cx, sandbox, _retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSIXPCONNECT_MOZILLA_1_8_BRANCH(_to) \
  NS_IMETHOD ReparentScopeAwareWrappers(JSContext * aJSContext, JSObject * aOldScope, JSObject * aNewScope) { return !_to ? NS_ERROR_NULL_POINTER : _to->ReparentScopeAwareWrappers(aJSContext, aOldScope, aNewScope); } \
  NS_IMETHOD CreateSandbox(JSContext * cx, nsIPrincipal *principal, nsIXPConnectJSObjectHolder **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->CreateSandbox(cx, principal, _retval); } \
  NS_IMETHOD EvalInSandboxObject(const nsAString & source, JSContext * cx, nsIXPConnectJSObjectHolder *sandbox, jsval *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->EvalInSandboxObject(source, cx, sandbox, _retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsXPConnect_MOZILLA_1_8_BRANCH : public nsIXPConnect_MOZILLA_1_8_BRANCH
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSIXPCONNECT_MOZILLA_1_8_BRANCH

  nsXPConnect_MOZILLA_1_8_BRANCH();

private:
  ~nsXPConnect_MOZILLA_1_8_BRANCH();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsXPConnect_MOZILLA_1_8_BRANCH, nsIXPConnect_MOZILLA_1_8_BRANCH)

nsXPConnect_MOZILLA_1_8_BRANCH::nsXPConnect_MOZILLA_1_8_BRANCH()
{
  /* member initializers and constructor code */
}

nsXPConnect_MOZILLA_1_8_BRANCH::~nsXPConnect_MOZILLA_1_8_BRANCH()
{
  /* destructor code */
}

/* void reparentScopeAwareWrappers (in JSContextPtr aJSContext, in JSObjectPtr aOldScope, in JSObjectPtr aNewScope); */
NS_IMETHODIMP nsXPConnect_MOZILLA_1_8_BRANCH::ReparentScopeAwareWrappers(JSContext * aJSContext, JSObject * aOldScope, JSObject * aNewScope)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* [noscript] nsIXPConnectJSObjectHolder createSandbox (in JSContextPtr cx, in nsIPrincipal principal); */
NS_IMETHODIMP nsXPConnect_MOZILLA_1_8_BRANCH::CreateSandbox(JSContext * cx, nsIPrincipal *principal, nsIXPConnectJSObjectHolder **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* [noscript] JSVal evalInSandboxObject (in AString source, in JSContextPtr cx, in nsIXPConnectJSObjectHolder sandbox); */
NS_IMETHODIMP nsXPConnect_MOZILLA_1_8_BRANCH::EvalInSandboxObject(const nsAString & source, JSContext * cx, nsIXPConnectJSObjectHolder *sandbox, jsval *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_nsIXPConnect_h__ */
