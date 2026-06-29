declare module "paytmchecksum" {
  class PaytmChecksum {
    static generateSignature(
      body: string,
      key: string
    ): Promise<string>;

    static verifySignature(
      body: string,
      key: string,
      checksum: string
    ): Promise<boolean>;
  }

  export default PaytmChecksum;
}
