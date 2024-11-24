export const isValidNumber = function (value) {
  return !Number.isNaN(Number(value));
};

export const compareVersion = function (v1, v2) {
  try {
    const a1 = v1.split('.');
    const a2 = v2.split('.');
    const len1 = a1.length;
    const len2 = a2.length;
    let times = Math.min(len1, len2);
    while (times--) {
      const value1 = a1[len1 - 1 - times];
      const value2 = a2[len2 - 1 - times];
      if (!isValidNumber(value1) || !isValidNumber(value2)) {
        continue;
      }
      const n1 = Number(value1);
      const n2 = Number(value2);
      if (n1 > n2) {
        return 1;
      }
      if (n1 < n2) {
        return -1;
      }
    }
  } catch (e) {}
  return 0;
};
