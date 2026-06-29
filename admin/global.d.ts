// import en from './messages/en.json';
import en from './public/locales/en.json';

type Messages = typeof en;

declare global {
  // Use type safe message keys with `next-intl`
  interface IntlMessages extends Messages {}
}
