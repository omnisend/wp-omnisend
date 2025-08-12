import { useState, useEffect } from "@wordpress/element";
import { CheckboxControl } from "@wordpress/components";
import apiFetch from "@wordpress/api-fetch";

const EmailServiceCheckbox = () => {
  const [checked, setChecked] = useState(
    omnisend_connected.omni_send_core_email_service_opt_in
  );

  const updateOption = (value) => {
    apiFetch({
      path: "/wp/v2/settings",
      method: "POST",
      data: { omni_send_core_email_service_opt_in: value },
    });
  };

  useEffect(() => {
    updateOption(checked ? 1 : 0);
  }, [checked]);

  return (
    <CheckboxControl
      id="email-service-opt-in"
      label="Use Omnisend for transactional emails"
      checked={checked}
      onChange={setChecked}
    />
  );
};

export default EmailServiceCheckbox;
