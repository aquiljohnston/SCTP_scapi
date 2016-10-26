CREATE TABLE [dbo].[tInspectorOQLog] (
    [tInspectorOQLogID] INT            IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [InspectorOQLogUID] VARCHAR (100)  NULL,
    [ProjectID]         INT            NULL,
    [SourceID]          VARCHAR (100)  NULL,
    [CreatedUserUID]    VARCHAR (100)  NULL,
    [ModifiedUserUID]   VARCHAR (100)  NULL,
    [CreateDTLT]        DATETIME       CONSTRAINT [DF_t_InspectorsOQLog_CreateDTLT] DEFAULT (getdate()) NULL,
    [ModifiedDTLT]      DATETIME       NULL,
    [Comments]          VARCHAR (2000) NULL,
    [RevisionComments]  VARCHAR (500)  NULL,
    [StatusType]        VARCHAR (200)  CONSTRAINT [DF_tInspectorOQLog_StatusType] DEFAULT ('Active') NULL,
    [Revision]          INT            CONSTRAINT [DF_t_InspectorsOQ_Revision] DEFAULT ((0)) NOT NULL,
    [ActiveFlag]        BIT            CONSTRAINT [DF_t_InspectorsOQLog_ActiveFlag] DEFAULT ((1)) NOT NULL,
    [UserUID]           VARCHAR (100)  NOT NULL,
    [OQSourceType]      VARCHAR (200)  NULL,
    [OQProfile]         VARCHAR (200)  NULL,
    [OQStartDate]       DATE           NULL,
    [OQEndDate]         DATE           NULL,
    [OQExpireDate]      DATE           NULL,
    CONSTRAINT [PK_t_InspectorOQLog] PRIMARY KEY CLUSTERED ([tInspectorOQLogID] ASC)
);

