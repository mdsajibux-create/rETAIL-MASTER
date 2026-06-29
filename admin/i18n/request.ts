import { getRequestConfig } from 'next-intl/server';

export default getRequestConfig(async () => {
  // This matches your BLIC_DEFAULT_LANGUAGE=en variable from your .env file
  const locale = process.env.BLIC_DEFAULT_LANGUAGE || 'en'; 

  return {
    locale,
    messages: (await import(`../messages/${locale}.json`)).default
  };
});