/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM imgIEncoder.idl
 */

#ifndef __gen_imgIEncoder_h__
#define __gen_imgIEncoder_h__


#ifndef __gen_nsISupports_h__
#include "nsISupports.h"
#endif

#ifndef __gen_nsIInputStream_h__
#include "nsIInputStream.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif

/* starting interface:    imgIEncoder_MOZILLA_1_8_BRANCH */
#define IMGIENCODER_MOZILLA_1_8_BRANCH_IID_STR "b1b0b493-3369-44e0-878d-f7c56d937680"

#define IMGIENCODER_MOZILLA_1_8_BRANCH_IID \
  {0xb1b0b493, 0x3369, 0x44e0, \
    { 0x87, 0x8d, 0xf7, 0xc5, 0x6d, 0x93, 0x76, 0x80 }}

/**
 * imgIEncoder interface
 */
class NS_NO_VTABLE imgIEncoder_MOZILLA_1_8_BRANCH : public nsIInputStream {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(IMGIENCODER_MOZILLA_1_8_BRANCH_IID)

  enum { INPUT_FORMAT_RGB = 0U };

  enum { INPUT_FORMAT_RGBA = 1U };

  enum { INPUT_FORMAT_HOSTARGB = 2U };

  /* void initFromData ([array, size_is (length), const] in PRUint8 data, in unsigned long length, in PRUint32 width, in PRUint32 height, in PRUint32 stride, in PRUint32 inputFormat, in AString outputOptions); */
  NS_IMETHOD InitFromData(const PRUint8 *data, PRUint32 length, PRUint32 width, PRUint32 height, PRUint32 stride, PRUint32 inputFormat, const nsAString & outputOptions) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_IMGIENCODER_MOZILLA_1_8_BRANCH \
  NS_IMETHOD InitFromData(const PRUint8 *data, PRUint32 length, PRUint32 width, PRUint32 height, PRUint32 stride, PRUint32 inputFormat, const nsAString & outputOptions); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_IMGIENCODER_MOZILLA_1_8_BRANCH(_to) \
  NS_IMETHOD InitFromData(const PRUint8 *data, PRUint32 length, PRUint32 width, PRUint32 height, PRUint32 stride, PRUint32 inputFormat, const nsAString & outputOptions) { return _to InitFromData(data, length, width, height, stride, inputFormat, outputOptions); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_IMGIENCODER_MOZILLA_1_8_BRANCH(_to) \
  NS_IMETHOD InitFromData(const PRUint8 *data, PRUint32 length, PRUint32 width, PRUint32 height, PRUint32 stride, PRUint32 inputFormat, const nsAString & outputOptions) { return !_to ? NS_ERROR_NULL_POINTER : _to->InitFromData(data, length, width, height, stride, inputFormat, outputOptions); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class _MYCLASS_ : public imgIEncoder_MOZILLA_1_8_BRANCH
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_IMGIENCODER_MOZILLA_1_8_BRANCH

  _MYCLASS_();

private:
  ~_MYCLASS_();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(_MYCLASS_, imgIEncoder_MOZILLA_1_8_BRANCH)

_MYCLASS_::_MYCLASS_()
{
  /* member initializers and constructor code */
}

_MYCLASS_::~_MYCLASS_()
{
  /* destructor code */
}

/* void initFromData ([array, size_is (length), const] in PRUint8 data, in unsigned long length, in PRUint32 width, in PRUint32 height, in PRUint32 stride, in PRUint32 inputFormat, in AString outputOptions); */
NS_IMETHODIMP _MYCLASS_::InitFromData(const PRUint8 *data, PRUint32 length, PRUint32 width, PRUint32 height, PRUint32 stride, PRUint32 inputFormat, const nsAString & outputOptions)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif

class nsIClipboardImage; /* forward declaration */

class nsIFile; /* forward declaration */


/* starting interface:    imgIEncoder */
#define IMGIENCODER_IID_STR "ccc5b3ad-3e67-4e3d-97e1-b06b2e96fef8"

#define IMGIENCODER_IID \
  {0xccc5b3ad, 0x3e67, 0x4e3d, \
    { 0x97, 0xe1, 0xb0, 0x6b, 0x2e, 0x96, 0xfe, 0xf8 }}

class NS_NO_VTABLE imgIEncoder : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(IMGIENCODER_IID)

  /* void encodeClipboardImage (in nsIClipboardImage aClipboardImage, out nsIFile aImageFile); */
  NS_IMETHOD EncodeClipboardImage(nsIClipboardImage *aClipboardImage, nsIFile **aImageFile) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_IMGIENCODER \
  NS_IMETHOD EncodeClipboardImage(nsIClipboardImage *aClipboardImage, nsIFile **aImageFile); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_IMGIENCODER(_to) \
  NS_IMETHOD EncodeClipboardImage(nsIClipboardImage *aClipboardImage, nsIFile **aImageFile) { return _to EncodeClipboardImage(aClipboardImage, aImageFile); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_IMGIENCODER(_to) \
  NS_IMETHOD EncodeClipboardImage(nsIClipboardImage *aClipboardImage, nsIFile **aImageFile) { return !_to ? NS_ERROR_NULL_POINTER : _to->EncodeClipboardImage(aClipboardImage, aImageFile); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class _MYCLASS_ : public imgIEncoder
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_IMGIENCODER

  _MYCLASS_();

private:
  ~_MYCLASS_();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(_MYCLASS_, imgIEncoder)

_MYCLASS_::_MYCLASS_()
{
  /* member initializers and constructor code */
}

_MYCLASS_::~_MYCLASS_()
{
  /* destructor code */
}

/* void encodeClipboardImage (in nsIClipboardImage aClipboardImage, out nsIFile aImageFile); */
NS_IMETHODIMP _MYCLASS_::EncodeClipboardImage(nsIClipboardImage *aClipboardImage, nsIFile **aImageFile)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_imgIEncoder_h__ */
