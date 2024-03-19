import {
	Card,
	CardBody,
	Flex,
	__experimentalText as Text,
	__experimentalHeading as Heading,
} from "@wordpress/components";

const AppsListNotice = () => {
	return (
		<Card isBorderless={true}>
			<CardBody isBorderless={true}>
				<Flex direction="column">
					<Heading level={1}>Omnisend Add-Ons</Heading>
					<Text size={14} style={{ maxWidth: "360px" }}>
						You can expand the possibilities of Omnisend by integrating it with
						additional add-ons.
					</Text>
				</Flex>
			</CardBody>
		</Card>
	);
};

export default AppsListNotice;
