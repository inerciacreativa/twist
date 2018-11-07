const path = require('path');
const HASH = () => require('crypto').createHash('md5').update(Date.now().toString(10), 'ut8').digest('hex');
const REGEXP_HASH = /\[hash(?::(\d+))?\]/i;

module.exports = (filename, template) => {
  let parsed = path.parse(filename);
  let name = template.replace('[name]', parsed.name);

  if (REGEXP_HASH.test(name)) {
    let result = REGEXP_HASH.exec(template)[1];

    name = name.replace(REGEXP_HASH, HASH().substring(0, parseInt(result, 10)));
  } else if (name.includes('[hash]')) {
    name = name.replace('[hash]', HASH());
  }

  return path.join(parsed.dir, name + parsed.ext);
};