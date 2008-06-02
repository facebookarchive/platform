/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM nsIControllers.idl
 */

#ifndef __gen_nsIControllers_h__
#define __gen_nsIControllers_h__


#ifndef __gen_nsIController_h__
#include "nsIController.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif
class nsIDOMXULCommandDispatcher; /* forward declaration */


/* starting interface:    nsIControllers */
#define NS_ICONTROLLERS_IID_STR "a5ed3a01-7cc7-11d3-bf87-00105a1b0627"

#define NS_ICONTROLLERS_IID \
  {0xa5ed3a01, 0x7cc7, 0x11d3, \
    { 0xbf, 0x87, 0x00, 0x10, 0x5a, 0x1b, 0x06, 0x27 }}

class NS_NO_VTABLE nsIControllers : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(NS_ICONTROLLERS_IID)

  /* attribute nsIDOMXULCommandDispatcher commandDispatcher; */
  NS_IMETHOD GetCommandDispatcher(nsIDOMXULCommandDispatcher * *aCommandDispatcher) = 0;
  NS_IMETHOD SetCommandDispatcher(nsIDOMXULCommandDispatcher * aCommandDispatcher) = 0;

  /* nsIController getControllerForCommand (in string command); */
  NS_IMETHOD GetControllerForCommand(const char *command, nsIController **_retval) = 0;

  /* void insertControllerAt (in unsigned long index, in nsIController controller); */
  NS_IMETHOD InsertControllerAt(PRUint32 index, nsIController *controller) = 0;

  /* nsIController removeControllerAt (in unsigned long index); */
  NS_IMETHOD RemoveControllerAt(PRUint32 index, nsIController **_retval) = 0;

  /* nsIController getControllerAt (in unsigned long index); */
  NS_IMETHOD GetControllerAt(PRUint32 index, nsIController **_retval) = 0;

  /* void appendController (in nsIController controller); */
  NS_IMETHOD AppendController(nsIController *controller) = 0;

  /* void removeController (in nsIController controller); */
  NS_IMETHOD RemoveController(nsIController *controller) = 0;

  /* unsigned long getControllerId (in nsIController controller); */
  NS_IMETHOD GetControllerId(nsIController *controller, PRUint32 *_retval) = 0;

  /* nsIController getControllerById (in unsigned long controllerID); */
  NS_IMETHOD GetControllerById(PRUint32 controllerID, nsIController **_retval) = 0;

  /* unsigned long getControllerCount (); */
  NS_IMETHOD GetControllerCount(PRUint32 *_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_NSICONTROLLERS \
  NS_IMETHOD GetCommandDispatcher(nsIDOMXULCommandDispatcher * *aCommandDispatcher); \
  NS_IMETHOD SetCommandDispatcher(nsIDOMXULCommandDispatcher * aCommandDispatcher); \
  NS_IMETHOD GetControllerForCommand(const char *command, nsIController **_retval); \
  NS_IMETHOD InsertControllerAt(PRUint32 index, nsIController *controller); \
  NS_IMETHOD RemoveControllerAt(PRUint32 index, nsIController **_retval); \
  NS_IMETHOD GetControllerAt(PRUint32 index, nsIController **_retval); \
  NS_IMETHOD AppendController(nsIController *controller); \
  NS_IMETHOD RemoveController(nsIController *controller); \
  NS_IMETHOD GetControllerId(nsIController *controller, PRUint32 *_retval); \
  NS_IMETHOD GetControllerById(PRUint32 controllerID, nsIController **_retval); \
  NS_IMETHOD GetControllerCount(PRUint32 *_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_NSICONTROLLERS(_to) \
  NS_IMETHOD GetCommandDispatcher(nsIDOMXULCommandDispatcher * *aCommandDispatcher) { return _to GetCommandDispatcher(aCommandDispatcher); } \
  NS_IMETHOD SetCommandDispatcher(nsIDOMXULCommandDispatcher * aCommandDispatcher) { return _to SetCommandDispatcher(aCommandDispatcher); } \
  NS_IMETHOD GetControllerForCommand(const char *command, nsIController **_retval) { return _to GetControllerForCommand(command, _retval); } \
  NS_IMETHOD InsertControllerAt(PRUint32 index, nsIController *controller) { return _to InsertControllerAt(index, controller); } \
  NS_IMETHOD RemoveControllerAt(PRUint32 index, nsIController **_retval) { return _to RemoveControllerAt(index, _retval); } \
  NS_IMETHOD GetControllerAt(PRUint32 index, nsIController **_retval) { return _to GetControllerAt(index, _retval); } \
  NS_IMETHOD AppendController(nsIController *controller) { return _to AppendController(controller); } \
  NS_IMETHOD RemoveController(nsIController *controller) { return _to RemoveController(controller); } \
  NS_IMETHOD GetControllerId(nsIController *controller, PRUint32 *_retval) { return _to GetControllerId(controller, _retval); } \
  NS_IMETHOD GetControllerById(PRUint32 controllerID, nsIController **_retval) { return _to GetControllerById(controllerID, _retval); } \
  NS_IMETHOD GetControllerCount(PRUint32 *_retval) { return _to GetControllerCount(_retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_NSICONTROLLERS(_to) \
  NS_IMETHOD GetCommandDispatcher(nsIDOMXULCommandDispatcher * *aCommandDispatcher) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCommandDispatcher(aCommandDispatcher); } \
  NS_IMETHOD SetCommandDispatcher(nsIDOMXULCommandDispatcher * aCommandDispatcher) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetCommandDispatcher(aCommandDispatcher); } \
  NS_IMETHOD GetControllerForCommand(const char *command, nsIController **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetControllerForCommand(command, _retval); } \
  NS_IMETHOD InsertControllerAt(PRUint32 index, nsIController *controller) { return !_to ? NS_ERROR_NULL_POINTER : _to->InsertControllerAt(index, controller); } \
  NS_IMETHOD RemoveControllerAt(PRUint32 index, nsIController **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->RemoveControllerAt(index, _retval); } \
  NS_IMETHOD GetControllerAt(PRUint32 index, nsIController **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetControllerAt(index, _retval); } \
  NS_IMETHOD AppendController(nsIController *controller) { return !_to ? NS_ERROR_NULL_POINTER : _to->AppendController(controller); } \
  NS_IMETHOD RemoveController(nsIController *controller) { return !_to ? NS_ERROR_NULL_POINTER : _to->RemoveController(controller); } \
  NS_IMETHOD GetControllerId(nsIController *controller, PRUint32 *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetControllerId(controller, _retval); } \
  NS_IMETHOD GetControllerById(PRUint32 controllerID, nsIController **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetControllerById(controllerID, _retval); } \
  NS_IMETHOD GetControllerCount(PRUint32 *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetControllerCount(_retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class nsControllers : public nsIControllers
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_NSICONTROLLERS

  nsControllers();

private:
  ~nsControllers();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(nsControllers, nsIControllers)

nsControllers::nsControllers()
{
  /* member initializers and constructor code */
}

nsControllers::~nsControllers()
{
  /* destructor code */
}

/* attribute nsIDOMXULCommandDispatcher commandDispatcher; */
NS_IMETHODIMP nsControllers::GetCommandDispatcher(nsIDOMXULCommandDispatcher * *aCommandDispatcher)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP nsControllers::SetCommandDispatcher(nsIDOMXULCommandDispatcher * aCommandDispatcher)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIController getControllerForCommand (in string command); */
NS_IMETHODIMP nsControllers::GetControllerForCommand(const char *command, nsIController **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void insertControllerAt (in unsigned long index, in nsIController controller); */
NS_IMETHODIMP nsControllers::InsertControllerAt(PRUint32 index, nsIController *controller)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIController removeControllerAt (in unsigned long index); */
NS_IMETHODIMP nsControllers::RemoveControllerAt(PRUint32 index, nsIController **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIController getControllerAt (in unsigned long index); */
NS_IMETHODIMP nsControllers::GetControllerAt(PRUint32 index, nsIController **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void appendController (in nsIController controller); */
NS_IMETHODIMP nsControllers::AppendController(nsIController *controller)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void removeController (in nsIController controller); */
NS_IMETHODIMP nsControllers::RemoveController(nsIController *controller)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* unsigned long getControllerId (in nsIController controller); */
NS_IMETHODIMP nsControllers::GetControllerId(nsIController *controller, PRUint32 *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* nsIController getControllerById (in unsigned long controllerID); */
NS_IMETHODIMP nsControllers::GetControllerById(PRUint32 controllerID, nsIController **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* unsigned long getControllerCount (); */
NS_IMETHODIMP nsControllers::GetControllerCount(PRUint32 *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_nsIControllers_h__ */
