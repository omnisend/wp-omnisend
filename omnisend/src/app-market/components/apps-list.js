import {
	Button,
	Card,
	CardHeader,
	CardBody,
	CardFooter,
	Flex,
	FlexItem,
	__experimentalSpacer as Spacer,
	__experimentalText as Text,
	__experimentalHeading as Heading,
} from "@wordpress/components";

const AppsList = ({ apps, categoryName, categoryDescription }) => {
	const navigateToPluginPage = (url) => {
		window.open(url, "_blank").focus();
	};

	return (
		<>
			<Spacer marginBottom={6}>
				{categoryName && <Heading>{categoryName}</Heading>}
				{categoryDescription && <Text>{categoryDescription}</Text>}
			</Spacer>
			<Flex
				gap={6}
				wrap={true}
				justify="start"
				style={{ margin: "auto", maxWidth: "950px" }}
			>
				{apps &&
					apps.map((app) => (
						<FlexItem key={app.slug}>
							<Card
								size={"medium"}
								isBorderless={true}
								backgroundSize={50}
								style={{ maxWidth: "300px" }}
							>
								<CardHeader isBorderless="true">
									<Flex direction="column">
										<img
											src={app.logo}
											style={{ width: "40px", height: "40px" }}
										/>
										<Heading level={4}>{app.name}</Heading>
										<Text size={12}>by {app.created_by}</Text>
									</Flex>
								</CardHeader>
								<CardBody>
									<Text size={14}>{app.description}</Text>
								</CardBody>
								<CardFooter isBorderless={true}>
									<Button
										variant="primary"
										onClick={() => navigateToPluginPage(app.url)}
									>
										Add this add-on
									</Button>
								</CardFooter>
							</Card>
						</FlexItem>
					))}
			</Flex>
		</>
	);
};

export default AppsList;
