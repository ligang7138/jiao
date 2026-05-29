export function formatMoney(value, digits = 2) {
  const number = Number(value || 0)
  return number.toFixed(digits)
}

export function calcLimitPrice(price, discountRate) {
  const value = Number(price || 0) * Number(discountRate || 0) + Number(price || 0)
  return Number(value.toFixed(2))
}
