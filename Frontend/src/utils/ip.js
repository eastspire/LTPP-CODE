/**
 * Checks if the current hostname is a public IP address and returns a formatted URL.
 * @param {string} protocol - The protocol to use (e.g., 'https', 'ws').
 * @param {number} port - The port number.
 * @returns {string|null} The formatted URL if it's a public IP, otherwise null.
 */
export function getPublicIpUrl(protocol, port) {
  const hostname = window.location.hostname;
  const isIpAddress =
    /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/.test(hostname) || hostname == 'localhost';
  if (isIpAddress) {
    const isPrivateIp =
      /^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.)/.test(hostname) ||
      hostname === '127.0.0.1' ||
      hostname == 'localhost';
    if (!isPrivateIp) {
      return `${protocol}://${hostname}:${port}`;
    }
  }
  if (protocol.includes('http')) {
    if (port == 48787) {
      return 'https://api.ltpp.vip';
    }
    if (port == 3000) {
      return window.sessionStorage.getItem('musicbkurl');
    }
  }
  if (protocol.includes('ws')) {
    return window.sessionStorage.getItem('socketurl');
  }
  return null;
}
